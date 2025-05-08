<?php
/**
 * BGFCI_Mapping - Handles mapping incoming webhook payloads to FluentCRM fields and orchestrates contact creation/update.
 *
 * @package BodyGraphFluentCRMIntegration
 */
if ( ! defined( 'ABSPATH' ) ) exit;

require_once __DIR__ . '/class-bgfci-logger.php';
require_once __DIR__ . '/class-bgfci-fluentcrm.php';

class BGFCI_Mapping {
    /**
     * Process a webhook payload and map to FluentCRM fields, then create/update contact.
     *
     * @param array $payload
     * @return array Log info
     */
    public static function process_fluentcrm_contact($payload) {
        // Log key fields from payload for debugging
        $log_fields = [
            'email' => $payload['EmailAddress'] ?? '',
            'first_name' => $payload['FirstName'] ?? '',
            'last_name' => $payload['LastName'] ?? '',
            'birth_time_local' => $payload['Properties']['BirthDateLocalStandard'] ?? '',
            'birth_time_utc' => $payload['Properties']['BirthDateUtcStandard'] ?? '',
            'birth_place' => $payload['BirthPlace'] ?? '',
            'type' => $payload['Properties']['Type']['option'] ?? '',
            'strategy' => $payload['Properties']['Strategy']['option'] ?? '',
            'inner_authority' => $payload['Properties']['InnerAuthority']['option'] ?? '',
            'profile' => $payload['Properties']['Profile']['option'] ?? '',
            'incarnation_cross' => $payload['Properties']['IncarnationCross']['option'] ?? ''
        ];
        BGFCI_Logger::log('Webhook payload summary: ' . json_encode($log_fields), 'info');

        // Standard FluentCRM fields
        $standard_fields = [
            'prefix' => $payload['NamePrefix'] ?? '',
            'first_name' => $payload['FirstName'] ?? '',
            'last_name' => $payload['LastName'] ?? '',
            'full_name' => $payload['Name'] ?? '',
            'email' => $payload['EmailAddress'] ?? '',
            'timezone' => $payload['Timezone'] ?? '',
            'address_line_1' => $payload['AddressLine1'] ?? '',
            'address_line_2' => $payload['AddressLine2'] ?? '',
            'city' => $payload['City'] ?? '',
            'state' => $payload['State'] ?? '',
            'postal_code' => $payload['PostalCode'] ?? '',
            'country' => $payload['Country'] ?? '',
            'ip' => $payload['IpAddress'] ?? '',
            'phone' => $payload['Phone'] ?? '',
            'source' => $payload['Source'] ?? '',
            'date_of_birth' => $payload['Properties']['BirthDateLocalStandard'] ?? '',
        ];
        // Custom fields
        $custom_fields = [
            'birth-place' => $payload['BirthPlace'] ?? '',
            'age' => $payload['Properties']['Age'] ?? '',
            'design-date-utc' => $payload['Properties']['DesignDateUtcStandard'] ?? '',
            'birth-date-local' => $payload['Properties']['BirthDateLocalStandard'] ?? '',
            'environment' => $payload['Properties']['Environment']['option'] ?? '',
            'perspective' => $payload['Properties']['Perspective']['option'] ?? '',
            'motivation' => $payload['Properties']['Motivation']['option'] ?? '',
            'design-sense' => $payload['Properties']['DesignSense']['option'] ?? '',
            'sense' => $payload['Properties']['Sense']['option'] ?? '',
            'digestion' => $payload['Properties']['Digestion']['option'] ?? '',
            'signature' => $payload['Properties']['Signature']['option'] ?? '',
            'not-self-theme' => $payload['Properties']['NotSelfTheme']['option'] ?? '',
            'birth-date-utc' => $payload['Properties']['BirthDateUtcStandard'] ?? '',
            'chart-url' => $payload['ChartUrl'] ?? '',
            'type' => $payload['Properties']['Type']['option'] ?? '',
            'strategy' => $payload['Properties']['Strategy']['option'] ?? '',
            'inner-authority' => $payload['Properties']['InnerAuthority']['option'] ?? '',
            'profile' => $payload['Properties']['Profile']['option'] ?? '',
            'incarnation-cross' => $payload['Properties']['IncarnationCross']['option'] ?? '',
        ];
        // List ID from settings
        $list_id = get_option('bgfci_fluentcrm_list_id', '');
        $lists = [];
        if (!empty($list_id)) {
            $lists[] = $list_id;
            BGFCI_Logger::log('Applying FluentCRM List ID: ' . $list_id, 'debug');
        }
        // For updates: only update custom fields, but always update date_of_birth (standard field)
        $custom_fields['__update_date_of_birth'] = $standard_fields['date_of_birth'];
        return BGFCI_FluentCRM::create_or_update_contact($standard_fields, $custom_fields, $lists);
    }
}
