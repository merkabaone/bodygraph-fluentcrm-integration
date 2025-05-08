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
    $params = $request->get_json_params();
    // Compose a simple log message
    $log_message = sprintf(
        'Webhook received at %s. Payload keys: %s',
        date('Y-m-d H:i:s'),
        is_array($params) ? implode(", ", array_keys($params)) : 'No JSON payload.'
    );
    error_log( '[BGFCI] ' . $log_message );
    // Return a response for debugging
    return rest_ensure_response([
        'success' => true,
        'message' => 'Webhook received',
        'received_at' => date('c'),
        'payload_keys' => is_array($params) ? array_keys($params) : [],
    ]);
}
