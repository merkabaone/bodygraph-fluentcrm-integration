<?php
/*
Plugin Name: BodyGraph FluentCRM Integration
Description: Minimal plugin to expose a REST API endpoint for receiving BodyGraph webhooks. v1.2 barebones.
Version: 1.2.0
Author: Erik Desrosiers
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

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
        
        // Log the complete request
        $log_message = sprintf(
            "Webhook received at %s\n" .
            "Raw Body: %s\n" .
            "Is JSON: %s\n" .
            "JSON Payload: %s",
            date('Y-m-d H:i:s'),
            $raw_body,
            $is_json ? 'Yes' : 'No',
            $is_json ? print_r($payload, true) : 'Not JSON'
        );
        
        error_log('[BGFCI] ' . $log_message);
        
        // Return response with detailed information
        return rest_ensure_response([
            'success' => true,
            'message' => 'Webhook received',
            'received_at' => date('c'),
            'raw_body' => $raw_body,
            'is_json' => $is_json,
            'payload' => $is_json ? $payload : null,
            'headers' => getallheaders()
        ]);
        
    } catch (Exception $e) {
        error_log('[BGFCI] Webhook error: ' . $e->getMessage());
        return rest_ensure_response([
            'success' => false,
            'message' => 'Error processing webhook',
            'error' => $e->getMessage()
        ], 500);
    }
}
