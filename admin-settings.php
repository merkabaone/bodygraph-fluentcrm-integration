<?php
// BGFCI Admin Settings Page for List ID
add_action('admin_menu', function() {
    add_options_page(
        'BodyGraph FluentCRM Integration',
        'BodyGraph FluentCRM',
        'manage_options',
        'bgfci-settings',
        'bgfci_render_settings_page'
    );
});

add_action('admin_init', function() {
    register_setting('bgfci_settings', 'bgfci_fluentcrm_list_id', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => ''
    ]);
    register_setting('bgfci_settings', 'bgfci_debug_logs', [
        'type' => 'boolean',
        'sanitize_callback' => function($val) { return $val ? 1 : 0; },
        'default' => 0
    ]);
});

/**
 * Render the BGFCI settings page with nonce and output escaping.
 */
function bgfci_render_settings_page() {
    if ( ! current_user_can('manage_options') ) {
        wp_die( esc_html__('You do not have sufficient permissions to access this page.', 'bodygraph-fluentcrm-integration') );
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('BodyGraph FluentCRM Integration Settings', 'bodygraph-fluentcrm-integration'); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields('bgfci_settings'); ?>
            <?php wp_nonce_field('bgfci_settings_save', 'bgfci_settings_nonce'); ?>
            <?php do_settings_sections('bgfci_settings'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('FluentCRM List ID', 'bodygraph-fluentcrm-integration'); ?></th>
                    <td>
                        <input type="text" name="bgfci_fluentcrm_list_id" value="<?php echo esc_attr(get_option('bgfci_fluentcrm_list_id', '')); ?>" />
                        <p class="description"><?php echo esc_html__('Enter the FluentCRM List ID to assign to new or updated contacts.', 'bodygraph-fluentcrm-integration'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php echo esc_html__('Enable Detailed Debug Logs', 'bodygraph-fluentcrm-integration'); ?></th>
                    <td>
                        <input type="checkbox" name="bgfci_debug_logs" value="1" <?php checked(1, get_option('bgfci_debug_logs', 0)); ?> />
                        <p class="description"><?php echo esc_html__('If checked, all debug-level logs will be written to the log file. Otherwise, only webhook summaries and contact update events will be logged.', 'bodygraph-fluentcrm-integration'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
