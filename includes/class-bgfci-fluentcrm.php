<?php
/**
 * BGFCI_FluentCRM - Modular FluentCRM integration for BodyGraph plugin
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class BGFCI_FluentCRM {
    /**
     * Create or update a FluentCRM contact from mapped fields
     *
     * @param array $standard_fields
     * @param array $custom_fields
     * @param array $lists
     * @return array [ 'log' => string, 'log_level' => string ]
     */
    public static function create_or_update_contact($standard_fields, $custom_fields, $lists = []) {
        if (!function_exists('FluentCrmApi')) {
            return ['log' => 'FluentCRM not installed or API missing.', 'log_level' => 'error'];
        }
        $email = $standard_fields['email'] ?? '';
        if (empty($email)) {
            return ['log' => 'No email found in webhook payload.', 'log_level' => 'warning'];
        }
        $api = FluentCrmApi('contacts');
        $contact = $api->getContact($email);
        if ($contact && !empty($contact->id)) {
            $update_data = $custom_fields;
            $update_data['email'] = $email;
            $existing_lists = isset($contact->lists) && is_array($contact->lists) ? $contact->lists : [];
            $merged_lists = array_unique(array_merge($existing_lists, $lists));
            if (!empty($merged_lists)) {
                $update_data['lists'] = $merged_lists;
                BGFCI_Logger::log('Final FluentCRM lists assigned to contact: ' . json_encode($merged_lists), 'debug');
            }
            BGFCI_Logger::log('Updating FluentCRM contact using createOrUpdate(). Data: ' . print_r($update_data, true), 'debug');
            $result = $api->createOrUpdate($update_data);
            if (empty($result) || !is_array($result)) {
                BGFCI_Logger::log('FluentCRM API createOrUpdate() returned empty or invalid response: ' . print_r($result, true), 'error');
            } else {
                BGFCI_Logger::log('FluentCRM API createOrUpdate() result: ' . print_r($result, true), 'debug');
            }
            return [
                'log' => "Updated FluentCRM contact custom fields (via createOrUpdate).",
                'log_level' => 'info'
            ];
        } else {
            $data = array_merge($standard_fields, [ 'custom_values' => $custom_fields ]);
            if (!empty($lists)) {
                $data['lists'] = $lists;
                BGFCI_Logger::log('Final FluentCRM lists assigned to contact: ' . implode(',', $lists), 'debug');
            }
            $result = $api->createOrUpdate($data);
            BGFCI_Logger::log('FluentCRM API createOrUpdate() result: ' . print_r($result, true), 'debug');
            return [
                'log' => "Created new FluentCRM contact for $email with standard & custom fields.",
                'log_level' => 'info'
            ];
        }
    }
}
