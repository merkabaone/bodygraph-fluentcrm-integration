<?php
/*
Plugin Name: BodyGraph FluentCRM Integration
Description: Minimal plugin to expose a REST API endpoint for receiving BodyGraph webhooks. v1.2 barebones.
Version: 1.2.9
Author: Erik Desrosiers
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

require_once __DIR__ . '/bgfcfi-fluentcrm-mapping.php';
require_once __DIR__ . '/admin-settings.php';
/**
 * Custom logging function for BGFCI plugin.
 * Logs only if WP_DEBUG is true.
 *
 * @param string $message The message to log.
 * @param string $level Log level: info, debug, warning, error.
 */
/**
 * Custom logging function for BGFCI plugin.
 * Logs to a dedicated file in the plugin directory (bgfci.log).
 * Falls back to error_log if file is not writable.
 *
 * Log file location: wp-content/plugins/bodygraph-fluentcrm-integration/bgfci.log
 *
 * @param string $message The message to log.
 * @param string $level Log level: info, debug, warning, error.
 */


require_once __DIR__ . '/bgfcfi-fluentcrm-mapping.php';
require_once __DIR__ . '/admin-settings.php';

// Test the logger on plugin load
add_action('plugins_loaded', function() {
    BGFCI_Logger::log('Test log: Plugin loaded successfully.', 'debug');
});

add_action( 'rest_api_init', function () {
    register_rest_route( 'bodygraph-fluentcrm/v1', '/webhook', array(
        'methods'  => 'POST',
        'callback' => 'bgfci_receive_webhook',
        'permission_callback' => '__return_true', // Open endpoint for webhook testing
    ));
});

function bgfci_receive_webhook( $request ) {
    try {
        // --- Simple IP-based rate limiting: max 10 requests per 10 minutes per IP ---
        $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : 'unknown';
        $transient_key = 'bgfci_webhook_ip_' . md5($ip);
        $count = (int) get_transient($transient_key);
        if ($count >= 10) {
            BGFCI_Logger::log("Rate limit exceeded for IP $ip", 'warning');
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Rate limit exceeded. Try again later.',
                'received_at' => date('c')
            ], 429);
        }
        set_transient($transient_key, $count + 1, 10 * MINUTE_IN_SECONDS);
        // -------------------------------------------------------------------------
        // Get raw request body
        $raw_body = file_get_contents('php://input');
        // Try to decode JSON
        $payload = json_decode($raw_body, true);
        $is_json = json_last_error() === JSON_ERROR_NONE;
        // Log that a webhook payload was received
        BGFCI_Logger::log('Webhook payload received at endpoint.', 'info');
        $result = null;
        $success = false;
        $message = '';
        // Sanitize and validate input
        if ($is_json && isset($payload['EmailAddress'])) {
            $payload['EmailAddress'] = sanitize_email($payload['EmailAddress']);
            $result = bgfci_process_fluentcrm_contact($payload);
            BGFCI_Logger::log($result['log'], $result['log_level']);
            $success = ($result['log_level'] === 'info');
            $message = $result['log'];
        } else {
            $message = 'Webhook payload missing or invalid email address.';
            BGFCI_Logger::log($message, 'warning');
        }
        if ($success) {
            return new WP_REST_Response([
                'success' => true,
                'message' => 'Payload received successfully.',
                'received_at' => date('c')
            ], 200);
        } else {
            return new WP_REST_Response([
                'success' => false,
                'message' => 'Invalid payload.',
                'received_at' => date('c')
            ], 400);
        }
    } catch (Exception $e) {
        BGFCI_Logger::log('Webhook processing exception: ' . $e->getMessage(), 'error');
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Internal server error',
            'received_at' => date('c')
        ], 500);
    }
}
