<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_init', 'barnahus_register_site_tools_settings');
add_action('admin_menu', 'barnahus_add_site_tools_settings_page');

function barnahus_register_site_tools_settings() {
    register_setting(
        'barnahus_site_tools',
        'barnahus_forum_preview_enabled',
        array(
            'type' => 'boolean',
            'sanitize_callback' => 'barnahus_sanitise_checkbox_setting',
            'default' => false,
        )
    );

    register_setting(
        'barnahus_site_tools',
        'barnahus_forum_content_approved',
        array(
            'type' => 'boolean',
            'sanitize_callback' => 'barnahus_sanitise_checkbox_setting',
            'default' => false,
        )
    );
}

function barnahus_sanitise_checkbox_setting($value) {
    return !empty($value) ? '1' : '0';
}

function barnahus_add_site_tools_settings_page() {
    add_options_page(
        'Barnahus site tools',
        'Barnahus site tools',
        'manage_options',
        'barnahus-site-tools',
        'barnahus_render_site_tools_settings_page'
    );
}

function barnahus_render_site_tools_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $routes_enabled = (bool) get_option('barnahus_forum_preview_enabled');
    $content_approved = (bool) get_option('barnahus_forum_content_approved');
    $is_live = $routes_enabled && $content_approved;
    ?>
    <div class="wrap">
        <h1>Barnahus site tools</h1>
        <?php settings_errors(); ?>

        <form action="options.php" method="post">
            <?php settings_fields('barnahus_site_tools'); ?>

            <h2>Forum programme</h2>
            <p>
                Status:
                <strong><?php echo esc_html($is_live ? 'Available on the website' : 'Not publicly available'); ?></strong>
            </p>

            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">Forum routes</th>
                        <td>
                            <input type="hidden" name="barnahus_forum_preview_enabled" value="0">
                            <label for="barnahus_forum_preview_enabled">
                                <input
                                    id="barnahus_forum_preview_enabled"
                                    name="barnahus_forum_preview_enabled"
                                    type="checkbox"
                                    value="1"
                                    <?php checked($routes_enabled); ?>
                                >
                                Publish the programme and participant routes
                            </label>
                            <p class="description">
                                Controls <code>/forum/programme</code>, <code>/forum/participants</code>, and their approved assets.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Content approval</th>
                        <td>
                            <input type="hidden" name="barnahus_forum_content_approved" value="0">
                            <label for="barnahus_forum_content_approved">
                                <input
                                    id="barnahus_forum_content_approved"
                                    name="barnahus_forum_content_approved"
                                    type="checkbox"
                                    value="1"
                                    <?php checked($content_approved); ?>
                                >
                                Confirm that the current programme and participant information is approved for publication
                            </label>
                            <p class="description">
                                Both settings must be selected before any Forum content is publicly available.
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php submit_button('Save Forum settings'); ?>
        </form>
    </div>
    <?php
}
