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
});

function bgfci_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>BodyGraph FluentCRM Integration Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('bgfci_settings'); ?>
            <?php do_settings_sections('bgfci_settings'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">FluentCRM List ID</th>
                    <td>
                        <input type="text" name="bgfci_fluentcrm_list_id" value="<?php echo esc_attr(get_option('bgfci_fluentcrm_list_id', '')); ?>" />
                        <p class="description">Enter the FluentCRM List ID to assign to new or updated contacts.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
