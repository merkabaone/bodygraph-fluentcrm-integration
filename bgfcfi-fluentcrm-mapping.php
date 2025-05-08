<?php
require_once __DIR__ . '/includes/class-bgfci-logger.php';
require_once __DIR__ . '/includes/class-bgfci-fluentcrm.php';
// Map incoming JSON to FluentCRM fields and create/update contact
function bgfci_process_fluentcrm_contact($payload) {
    // Log that a payload was received
    // Log key fields from payload for debugging
    $log_fields = [
        'email' => $payload['EmailAddress'] ?? '',
        'first_name' => $payload['FirstName'] ?? '',
        'last_name' => $payload['LastName'] ?? '',
        'birth_time_local' => $payload['Properties']['BirthDateLocalStandard'] ?? '',
        'birth_time_utc' => $payload['Properties']['BirthDateUtcStandard'] ?? '',
        'birth_place' => $payload['BirthPlace'] ?? ''
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
        'definition' => $payload['Properties']['Definition']['option'] ?? '',
        'company' => $payload['Company'] ?? '',
        'role' => $payload['Role'] ?? '',
        'payload' => json_encode($payload), // Store full payload as string
    ];
    // Log the mapped fields to be sent to FluentCRM
    BGFCI_Logger::log('Mapping webhook payload to FluentCRM fields for processing.', 'debug');
    // Apply FluentCRM List ID from settings if set
    $list_id = get_option('bgfci_fluentcrm_list_id');
    $lists = [];
    if (!empty($list_id)) {
        $lists[] = $list_id;
        BGFCI_Logger::log('Applying FluentCRM List ID: ' . $list_id, 'debug');
    }
    // Delegate to modular FluentCRM integration
    return BGFCI_FluentCRM::create_or_update_contact($standard_fields, $custom_fields, $lists);
}
