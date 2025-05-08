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
function bgfci_log($message, $level = 'info') {
    $log_dir = plugin_dir_path(__FILE__);
    $log_file = $log_dir . 'bgfci.log';
    $timestamp = date('Y-m-d H:i:s');
    $entry = "[$timestamp][" . strtoupper($level) . "] $message\n";

    // Try to write to the custom log file
    $written = false;
    if (is_writable($log_dir) || (!file_exists($log_file) && is_writable($log_dir))) {
        $fp = @fopen($log_file, 'a');
        if ($fp) {
            if (flock($fp, LOCK_EX)) {
                fwrite($fp, $entry);
                flock($fp, LOCK_UN);
                $written = true;
            }
            fclose($fp);
        }
    }
    // Fallback to error_log if unable to write to file
    if (! $written) {
        error_log('[BGFCI][' . strtoupper($level) . "] $message");
    }
}

require_once __DIR__ . '/bgfcfi-fluentcrm-mapping.php';
require_once __DIR__ . '/admin-settings.php';

// Test the logger on plugin load
add_action('plugins_loaded', function() {
    bgfci_log('Test log: Plugin loaded successfully.', 'debug');
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
        // Get raw request body
        $raw_body = file_get_contents('php://input');
        
        // Try to decode JSON
        $payload = json_decode($raw_body, true);
        $is_json = json_last_error() === JSON_ERROR_NONE;
        
        // Log that a webhook payload was received
        bgfci_log('Webhook payload received at endpoint.', 'info');

        $result = null;
        $success = false;
        $message = '';
        if ($is_json && isset($payload['EmailAddress'])) {
            $result = bgfci_process_fluentcrm_contact($payload);
            bgfci_log($result['log'], $result['log_level']);
            $success = ($result['log_level'] === 'info');
            $message = $result['log'];
        } else {
            $message = 'Webhook payload missing or invalid email address.';
            bgfci_log($message, 'warning');
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
        bgfci_log('Webhook processing exception: ' . $e->getMessage(), 'error');
        return new WP_REST_Response([
            'success' => false,
            'message' => 'Internal server error',
            'received_at' => date('c')
        ], 500);
    }
}
