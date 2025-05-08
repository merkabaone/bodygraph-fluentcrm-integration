<?php
// Map incoming JSON to FluentCRM fields and create/update contact
function bgfci_process_fluentcrm_contact($payload) {
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
        $result = $api->update($contact->id, [ 'custom_values' => $custom_fields ]);
        return [
            'log' => "Updated custom fields for existing FluentCRM contact ID {$contact->id} (email: $email)",
            'log_level' => 'info'
        ];
    } else {
        // Create new contact with all fields
        $data = array_merge($standard_fields, [ 'custom_values' => $custom_fields ]);
        $result = $api->createOrUpdate($data);
        return [
            'log' => "Created new FluentCRM contact for $email with standard & custom fields.",
            'log_level' => 'info'
        ];
    }
}
