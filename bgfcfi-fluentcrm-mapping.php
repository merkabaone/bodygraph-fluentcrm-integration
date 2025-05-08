<?php
// Map incoming JSON to FluentCRM fields and create/update contact
function bgfci_process_fluentcrm_contact($payload) {
    // Log that a payload was received
    bgfci_log('Webhook payload received for FluentCRM processing.', 'info');
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
    ];
    // Log the mapped fields to be sent to FluentCRM
    bgfci_log('Mapping webhook payload to FluentCRM fields for processing.', 'debug');
    // Apply FluentCRM List ID from settings if set
    $list_id = get_option('bgfci_fluentcrm_list_id');
    $lists = [];
    if (!empty($list_id)) {
        $lists[] = $list_id;
        bgfci_log('Applying FluentCRM List ID: ' . $list_id, 'debug');
    }
    // FluentCRM API
    if (!function_exists('FluentCrmApi')) {
        return ['log' => 'FluentCRM not installed or API missing.', 'log_level' => 'error'];
    }
    $email = $standard_fields['email'];
    if (empty($email)) {
        return ['log' => 'No email found in webhook payload.', 'log_level' => 'warning'];
    }
    $api = FluentCrmApi('contacts');
    $contact = $api->getContact($email);
    if ($contact && !empty($contact->id)) {
        // Update only custom fields
        // Get existing lists and merge with the new one (if not already present)
        $existing_lists = isset($contact->lists) && is_array($contact->lists) ? $contact->lists : [];
        $merged_lists = array_unique(array_merge($existing_lists, $lists));
        $update_data = [ 'custom_values' => $custom_fields ];
        if (!empty($merged_lists)) {
            $update_data['lists'] = $merged_lists;
            bgfci_log('Final FluentCRM lists assigned to contact: ' . implode(',', $merged_lists), 'debug');
        }
        $result = $api->update($contact->id, $update_data);
        bgfci_log('FluentCRM API update() result: ' . print_r($result, true), 'debug');
        return [
            'log' => "Updated custom fields for existing FluentCRM contact ID {$contact->id} (email: $email)",
            'log_level' => 'info'
        ];
    } else {
        // Create new contact with all fields
        $data = array_merge($standard_fields, [ 'custom_values' => $custom_fields ]);
        if (!empty($lists)) {
            $data['lists'] = $lists;
            bgfci_log('Final FluentCRM lists assigned to contact: ' . implode(',', $lists), 'debug');
        }
        $result = $api->createOrUpdate($data);
        bgfci_log('FluentCRM API createOrUpdate() result: ' . print_r($result, true), 'debug');
        return [
            'log' => "Created new FluentCRM contact for $email with standard & custom fields.",
            'log_level' => 'info'
        ];
    }
}
