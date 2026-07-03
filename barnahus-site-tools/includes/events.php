<?php

if (!defined('ABSPATH')) {
    exit;
}

const BARNAHUS_EVENT_POST_TYPE = 'barnahus_event';
const BARNAHUS_EVENT_SERIES_TAXONOMY = 'barnahus_event_series';
const BARNAHUS_EVENT_CANONICAL_POST_TYPE = 'post';
const BARNAHUS_EVENT_TAG_SLUG = 'event';
const BARNAHUS_EVENT_LUMA_CALENDAR_URL = 'https://luma.com/Barnahus';
const BARNAHUS_EVENT_REWRITE_VERSION = '2026-07-03-public-events';
const BARNAHUS_EVENT_SNAPSHOTS_OPTION = 'barnahus_event_dashboard_snapshots';
const BARNAHUS_EVENT_SNAPSHOT_LIMIT = 20;

add_action('init', 'barnahus_register_event_content');
add_action('init', 'barnahus_maybe_flush_event_rewrite_rules', 99);
add_action('admin_menu', 'barnahus_add_events_dashboard_page');
add_action('admin_post_barnahus_save_events_dashboard', 'barnahus_save_events_dashboard');
add_action('admin_post_barnahus_create_event_from_dashboard', 'barnahus_create_event_from_dashboard');
add_action('admin_post_barnahus_refresh_luma_events', 'barnahus_refresh_luma_events_from_dashboard');
add_action('admin_post_barnahus_convert_event_pages_to_posts', 'barnahus_convert_event_pages_to_posts_from_dashboard');
add_action('admin_post_barnahus_restore_event_snapshot', 'barnahus_restore_event_snapshot_from_dashboard');
add_action('add_meta_boxes', 'barnahus_add_event_details_meta_box');
add_action('add_meta_boxes', 'barnahus_add_event_usage_meta_box');
add_action('save_post_' . BARNAHUS_EVENT_POST_TYPE, 'barnahus_save_event_details');
add_action('save_post_' . BARNAHUS_EVENT_CANONICAL_POST_TYPE, 'barnahus_save_event_details');
add_filter('manage_' . BARNAHUS_EVENT_POST_TYPE . '_posts_columns', 'barnahus_event_admin_columns');
add_action('manage_' . BARNAHUS_EVENT_POST_TYPE . '_posts_custom_column', 'barnahus_event_admin_column_content', 10, 2);
add_filter('manage_edit-' . BARNAHUS_EVENT_POST_TYPE . '_sortable_columns', 'barnahus_event_sortable_admin_columns');
add_action('pre_get_posts', 'barnahus_event_admin_orderby');
add_filter('the_content', 'barnahus_render_event_single_content');
add_shortcode('barnahus_events', 'barnahus_events_shortcode');
add_shortcode('barnahus_event_card', 'barnahus_event_card_shortcode');

function barnahus_register_event_content() {
    register_post_type(
        BARNAHUS_EVENT_POST_TYPE,
        array(
            'labels' => array(
                'name' => 'Barnahus Events',
                'singular_name' => 'Barnahus Event',
                'add_new_item' => 'Add New Barnahus Event',
                'edit_item' => 'Edit Barnahus Event',
                'new_item' => 'New Barnahus Event',
                'view_item' => 'View Barnahus Event',
                'search_items' => 'Search Barnahus Events',
                'not_found' => 'No Barnahus events found',
            ),
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => false,
            'supports' => array('title', 'editor', 'excerpt'),
            'has_archive' => false,
            'exclude_from_search' => true,
            'rewrite' => array(
                'slug' => 'events',
                'with_front' => false,
            ),
            'show_in_rest' => true,
        )
    );

    register_taxonomy(
        BARNAHUS_EVENT_SERIES_TAXONOMY,
        BARNAHUS_EVENT_POST_TYPE,
        array(
            'labels' => array(
                'name' => 'Event Series',
                'singular_name' => 'Event Series',
                'search_items' => 'Search Event Series',
                'all_items' => 'All Event Series',
                'edit_item' => 'Edit Event Series',
                'update_item' => 'Update Event Series',
                'add_new_item' => 'Add New Event Series',
                'new_item_name' => 'New Event Series Name',
            ),
            'hierarchical' => false,
            'show_ui' => false,
            'show_admin_column' => true,
            'show_in_rest' => true,
        )
    );
}

function barnahus_maybe_flush_event_rewrite_rules() {
    if (get_option('barnahus_event_rewrite_version') === BARNAHUS_EVENT_REWRITE_VERSION) {
        return;
    }

    flush_rewrite_rules(false);
    update_option('barnahus_event_rewrite_version', BARNAHUS_EVENT_REWRITE_VERSION);
}

function barnahus_add_events_dashboard_page() {
    add_menu_page(
        'Barnahus Events',
        'Barnahus Events',
        'edit_posts',
        'barnahus-event-dashboard',
        'barnahus_render_events_dashboard_page',
        'dashicons-calendar-alt',
        26
    );
}

function barnahus_get_events_dashboard_url($args = array()) {
    $url = admin_url('admin.php?page=barnahus-event-dashboard');

    if ($args) {
        $url = add_query_arg($args, $url);
    }

    return $url;
}

function barnahus_get_event_dashboard_snapshots() {
    $snapshots = get_option(BARNAHUS_EVENT_SNAPSHOTS_OPTION, array());

    return is_array($snapshots) ? $snapshots : array();
}

function barnahus_capture_event_dashboard_snapshot($label) {
    $events = barnahus_get_events_for_dashboard();

    if (!$events) {
        return '';
    }

    $snapshot = array(
        'id' => function_exists('wp_generate_uuid4') ? wp_generate_uuid4() : uniqid('event-snapshot-', true),
        'created_at' => time(),
        'label' => sanitize_text_field($label),
        'events' => array(),
    );

    foreach ($events as $event) {
        $post_id = absint($event->ID);

        $snapshot['events'][$post_id] = array(
            'post_id' => $post_id,
            'post_type' => get_post_type($post_id),
            'post_status' => get_post_status($post_id),
            'title' => get_post_field('post_title', $post_id),
            'excerpt' => get_post_field('post_excerpt', $post_id),
            'series' => barnahus_get_event_series_names($post_id),
            'meta' => barnahus_get_event_snapshot_meta($post_id),
        );
    }

    $snapshots = barnahus_get_event_dashboard_snapshots();
    array_unshift($snapshots, $snapshot);
    $snapshots = array_slice($snapshots, 0, BARNAHUS_EVENT_SNAPSHOT_LIMIT);

    update_option(BARNAHUS_EVENT_SNAPSHOTS_OPTION, $snapshots, false);

    return $snapshot['id'];
}

function barnahus_get_event_snapshot_meta($post_id) {
    $meta_keys = array(
        '_barnahus_event_date',
        '_barnahus_event_start_time',
        '_barnahus_event_end_time',
        '_barnahus_event_location',
        '_barnahus_event_luma_url',
        '_barnahus_event_custom_url',
        '_barnahus_event_card_link_type',
        '_barnahus_event_registration_status',
        '_barnahus_event_featured',
        '_barnahus_event_pinned',
        '_barnahus_event_hidden',
    );

    $meta = array();

    foreach ($meta_keys as $meta_key) {
        $meta[$meta_key] = get_post_meta($post_id, $meta_key, true);
    }

    return $meta;
}

function barnahus_find_event_dashboard_snapshot($snapshot_id) {
    $snapshot_id = sanitize_text_field($snapshot_id);

    foreach (barnahus_get_event_dashboard_snapshots() as $snapshot) {
        if (!empty($snapshot['id']) && $snapshot_id === $snapshot['id']) {
            return $snapshot;
        }
    }

    return array();
}

function barnahus_restore_event_snapshot_from_dashboard() {
    if (!current_user_can('edit_posts')) {
        wp_die('You do not have permission to restore event history.');
    }

    if (!isset($_POST['barnahus_restore_event_snapshot_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['barnahus_restore_event_snapshot_nonce'])), 'barnahus_restore_event_snapshot')) {
        wp_die('The event history form could not be verified.');
    }

    $snapshot_id = isset($_POST['snapshot_id']) ? sanitize_text_field(wp_unslash($_POST['snapshot_id'])) : '';
    $snapshot = barnahus_find_event_dashboard_snapshot($snapshot_id);

    if (!$snapshot || empty($snapshot['events']) || !is_array($snapshot['events'])) {
        wp_safe_redirect(barnahus_get_events_dashboard_url(array('barnahus_event_restore_error' => 'missing')));
        exit;
    }

    barnahus_capture_event_dashboard_snapshot('Before history restore');

    $restored = 0;

    foreach ($snapshot['events'] as $event_data) {
        if (empty($event_data['post_id'])) {
            continue;
        }

        $post_id = absint($event_data['post_id']);

        if (!$post_id || !get_post($post_id) || !current_user_can('edit_post', $post_id)) {
            continue;
        }

        wp_update_post(
            array(
                'ID' => $post_id,
                'post_title' => isset($event_data['title']) ? sanitize_text_field($event_data['title']) : get_the_title($post_id),
                'post_excerpt' => isset($event_data['excerpt']) ? sanitize_textarea_field($event_data['excerpt']) : '',
            )
        );

        if (!empty($event_data['meta']) && is_array($event_data['meta'])) {
            foreach ($event_data['meta'] as $meta_key => $meta_value) {
                update_post_meta($post_id, sanitize_key($meta_key), is_string($meta_value) ? wp_kses_post($meta_value) : $meta_value);
            }
        }

        if (isset($event_data['series'])) {
            barnahus_set_event_series_names($post_id, $event_data['series']);
        }

        $restored++;
    }

    wp_safe_redirect(barnahus_get_events_dashboard_url(array('barnahus_event_restored' => '1', 'restored' => $restored)));
    exit;
}

function barnahus_get_event_post_types() {
    return array(BARNAHUS_EVENT_CANONICAL_POST_TYPE, BARNAHUS_EVENT_POST_TYPE);
}

function barnahus_is_event_post($post_id) {
    $post_type = get_post_type($post_id);

    if (BARNAHUS_EVENT_POST_TYPE === $post_type) {
        return true;
    }

    if (BARNAHUS_EVENT_CANONICAL_POST_TYPE !== $post_type) {
        return false;
    }

    return has_tag(BARNAHUS_EVENT_TAG_SLUG, $post_id) || metadata_exists('post', $post_id, '_barnahus_event_date');
}

function barnahus_get_events_for_dashboard() {
    $events = get_posts(
        array(
            'post_type' => barnahus_get_event_post_types(),
            'post_status' => array('publish', 'draft', 'pending', 'future'),
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => '_barnahus_event_date',
                    'compare' => 'EXISTS',
                ),
                array(
                    'key' => '_barnahus_event_luma_url',
                    'compare' => 'EXISTS',
                ),
            ),
            'no_found_rows' => true,
        )
    );

    $events = array_filter($events, 'barnahus_event_post_is_dashboard_event');

    usort(
        $events,
        function ($event_a, $event_b) {
            return barnahus_compare_events_for_display($event_a, $event_b, 'chronological');
        }
    );

    return $events;
}

function barnahus_event_post_is_dashboard_event($event) {
    return $event instanceof WP_Post && barnahus_is_event_post($event->ID);
}

function barnahus_get_legacy_event_posts() {
    return get_posts(
        array(
            'post_type' => BARNAHUS_EVENT_POST_TYPE,
            'post_status' => array('publish', 'draft', 'pending', 'future'),
            'posts_per_page' => -1,
            'fields' => 'ids',
            'no_found_rows' => true,
        )
    );
}

function barnahus_render_events_dashboard_page() {
    if (!current_user_can('edit_posts')) {
        return;
    }

    $events = barnahus_get_events_for_dashboard();
    $legacy_events = barnahus_get_legacy_event_posts();
    $last_luma_refresh = get_option('barnahus_event_luma_last_refresh');
    $snapshots = barnahus_get_event_dashboard_snapshots();

    ?>
    <div class="wrap">
        <h1>Event Dashboard</h1>
        <p>Use this screen to manage how events appear publicly. Event Series are stored as regular post tags alongside the required event tag.</p>

        <?php if (isset($_GET['barnahus_events_saved'])) : ?>
            <div class="notice notice-success is-dismissible">
                <p>Event display settings saved.</p>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['barnahus_event_created'])) : ?>
            <div class="notice notice-success is-dismissible">
                <p>Event card created.</p>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['barnahus_event_restored'])) : ?>
            <div class="notice notice-success is-dismissible">
                <p>Event dashboard history restored for <?php echo esc_html(absint($_GET['restored'])); ?> event(s).</p>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['barnahus_event_restore_error'])) : ?>
            <div class="notice notice-error is-dismissible">
                <p>That event dashboard history point could not be found.</p>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['barnahus_luma_refreshed'])) : ?>
            <div class="notice notice-success is-dismissible">
                <p>Luma refresh complete. Added <?php echo esc_html(absint($_GET['created'])); ?> event post(s), updated <?php echo esc_html(absint($_GET['updated'])); ?>, and skipped <?php echo esc_html(absint($_GET['skipped'])); ?>.</p>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['barnahus_luma_error'])) : ?>
            <div class="notice notice-error is-dismissible">
                <p>Luma refresh could not find event data on the public calendar page. Nothing was changed.</p>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['barnahus_events_converted'])) : ?>
            <div class="notice notice-success is-dismissible">
                <p>Converted <?php echo esc_html(absint($_GET['converted'])); ?> existing event page(s) into regular WordPress posts tagged event.</p>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['barnahus_event_error']) && 'missing_title' === $_GET['barnahus_event_error']) : ?>
            <div class="notice notice-error is-dismissible">
                <p>Please add an event title before creating an event card.</p>
            </div>
        <?php endif; ?>

        <style>
            .barnahus-event-dashboard {
                display: grid;
                gap: 16px;
                max-width: 1280px;
            }

            .barnahus-event-dashboard-card,
            .barnahus-event-dashboard-tools,
            .barnahus-event-dashboard-create {
                background: #fff;
                border: 1px solid #c3c4c7;
                padding: 16px;
            }

            .barnahus-event-dashboard-tools {
                display: flex;
                flex-wrap: wrap;
                gap: 12px 20px;
                align-items: center;
                justify-content: space-between;
                margin: 16px 0;
            }

            .barnahus-event-dashboard-tools__actions {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                align-items: center;
            }

            .barnahus-event-dashboard-tools p {
                margin: 0;
            }

            .barnahus-event-dashboard-create {
                margin: 16px 0;
                border-left: 4px solid #aeb9ee;
            }

            .barnahus-event-dashboard-history {
                margin: 0 0 16px;
                background: #fff;
                border: 1px solid #c3c4c7;
                padding: 12px 16px;
            }

            .barnahus-event-dashboard-history summary {
                cursor: pointer;
                font-weight: 600;
            }

            .barnahus-event-dashboard-history__list {
                display: grid;
                gap: 8px;
                margin-top: 12px;
            }

            .barnahus-event-dashboard-history__item {
                display: flex;
                flex-wrap: wrap;
                gap: 8px 16px;
                align-items: center;
                justify-content: space-between;
                border-top: 1px solid #dcdcde;
                padding-top: 8px;
            }

            .barnahus-event-dashboard-history__item p {
                margin: 0;
            }

            .barnahus-event-dashboard-create__title {
                margin: 0 0 12px;
                font-size: 16px;
            }

            .barnahus-event-dashboard-card__header {
                display: flex;
                flex-wrap: wrap;
                gap: 8px 16px;
                align-items: baseline;
                justify-content: space-between;
                margin-bottom: 14px;
            }

            .barnahus-event-dashboard-card__title {
                margin: 0;
                font-size: 16px;
            }

            .barnahus-event-dashboard-card__meta {
                color: #646970;
            }

            .barnahus-event-dashboard-card__fields {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 14px;
                align-items: start;
            }

            .barnahus-event-dashboard-card__field {
                display: grid;
                gap: 5px;
            }

            .barnahus-event-dashboard-card__field label {
                font-weight: 600;
            }

            .barnahus-event-dashboard-card__field .description {
                margin: 0;
                color: #646970;
            }

            .barnahus-event-dashboard-card__link-note {
                margin: 6px 0 0;
                max-width: 760px;
                color: #646970;
            }

            .barnahus-event-dashboard-card__field input[type="date"],
            .barnahus-event-dashboard-card__field input[type="text"],
            .barnahus-event-dashboard-card__field input[type="url"],
            .barnahus-event-dashboard-card__field select,
            .barnahus-event-dashboard-card__field textarea {
                width: 100%;
                max-width: none;
                box-sizing: border-box;
            }

            .barnahus-event-dashboard-card__field input[type="date"],
            .barnahus-event-dashboard-card__field input[type="text"],
            .barnahus-event-dashboard-card__field input[type="url"],
            .barnahus-event-dashboard-card__field select {
                min-height: 40px;
            }

            .barnahus-event-dashboard-card__toggles {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                align-content: start;
            }

            .barnahus-event-dashboard-card__full {
                grid-column: 1 / -1;
            }

            .barnahus-event-dashboard-card__link-settings {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
                gap: 14px;
                align-items: end;
            }

            @media (max-width: 782px) {
                .barnahus-event-dashboard-card__full {
                    grid-column: auto;
                }
            }
        </style>

        <div class="barnahus-event-dashboard-tools">
            <p>
                Luma source: <a href="<?php echo esc_url(BARNAHUS_EVENT_LUMA_CALENDAR_URL); ?>">Network Events</a>
                <?php if ($last_luma_refresh) : ?>
                    <br><span class="description">Last refreshed <?php echo esc_html(date_i18n('j F Y H:i', (int) $last_luma_refresh)); ?>.</span>
                <?php endif; ?>
            </p>
            <div class="barnahus-event-dashboard-tools__actions">
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="barnahus_refresh_luma_events">
                    <?php wp_nonce_field('barnahus_refresh_luma_events', 'barnahus_refresh_luma_nonce'); ?>
                    <?php submit_button('Refresh from Luma', 'secondary', 'submit', false); ?>
                </form>

                <?php if ($legacy_events) : ?>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <input type="hidden" name="action" value="barnahus_convert_event_pages_to_posts">
                        <?php wp_nonce_field('barnahus_convert_event_pages_to_posts', 'barnahus_convert_events_nonce'); ?>
                        <?php submit_button('Convert existing event pages to posts', 'secondary', 'submit', false); ?>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($snapshots) : ?>
            <details class="barnahus-event-dashboard-history">
                <summary>Version history</summary>
                <p class="description">A restore point is saved before each dashboard save, Luma refresh, and history restore. Restoring brings back saved event-card settings for events that still exist.</p>
                <div class="barnahus-event-dashboard-history__list">
                    <?php foreach (array_slice($snapshots, 0, 8) as $snapshot) : ?>
                        <div class="barnahus-event-dashboard-history__item">
                            <p>
                                <strong><?php echo esc_html(isset($snapshot['label']) ? $snapshot['label'] : 'Event dashboard snapshot'); ?></strong>
                                <br>
                                <span class="description">
                                    <?php echo esc_html(date_i18n('j F Y H:i', isset($snapshot['created_at']) ? (int) $snapshot['created_at'] : time())); ?>
                                    · <?php echo esc_html(isset($snapshot['events']) && is_array($snapshot['events']) ? count($snapshot['events']) : 0); ?> event(s)
                                </span>
                            </p>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                <input type="hidden" name="action" value="barnahus_restore_event_snapshot">
                                <input type="hidden" name="snapshot_id" value="<?php echo esc_attr(isset($snapshot['id']) ? $snapshot['id'] : ''); ?>">
                                <?php wp_nonce_field('barnahus_restore_event_snapshot', 'barnahus_restore_event_snapshot_nonce'); ?>
                                <?php submit_button('Restore', 'secondary', 'submit', false); ?>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </details>
        <?php endif; ?>

        <form class="barnahus-event-dashboard-create" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="barnahus_create_event_from_dashboard">
            <?php wp_nonce_field('barnahus_create_event_from_dashboard', 'barnahus_create_event_nonce'); ?>

            <h2 class="barnahus-event-dashboard-create__title">Add planned event card</h2>

            <div class="barnahus-event-dashboard-card__fields">
                <div class="barnahus-event-dashboard-card__field">
                    <label for="barnahus_new_event_title">Event title</label>
                    <input type="text" id="barnahus_new_event_title" name="new_event[title]" placeholder="Working title">
                </div>

                <div class="barnahus-event-dashboard-card__field">
                    <label for="barnahus_new_event_date">Date</label>
                    <input type="date" id="barnahus_new_event_date" name="new_event[date]">
                </div>

                <div class="barnahus-event-dashboard-card__field">
                    <label for="barnahus_new_event_location">Location / platform</label>
                    <input type="text" id="barnahus_new_event_location" name="new_event[location]" placeholder="Online, city, or TBA">
                </div>

                <div class="barnahus-event-dashboard-card__field">
                    <label for="barnahus_new_event_series">Event Series</label>
                    <input type="text" id="barnahus_new_event_series" name="new_event[series]" placeholder="Webinars, Training">
                </div>

                <div class="barnahus-event-dashboard-card__field">
                    <label for="barnahus_new_event_registration_status">Registration status</label>
                    <select id="barnahus_new_event_registration_status" name="new_event[registration_status]">
                        <?php foreach (barnahus_get_registration_status_options() as $status_value => $status_label) : ?>
                            <option value="<?php echo esc_attr($status_value); ?>" <?php selected('coming-soon', $status_value); ?>><?php echo esc_html($status_label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="barnahus-event-dashboard-card__field barnahus-event-dashboard-card__toggles">
                    <label>
                        <input type="checkbox" name="new_event[featured]" value="1">
                        Featured
                    </label>
                    <label>
                        <input type="checkbox" name="new_event[pinned]" value="1" checked>
                        Pinned to top
                    </label>
                </div>

                <div class="barnahus-event-dashboard-card__field barnahus-event-dashboard-card__full">
                    <label for="barnahus_new_event_excerpt">Card intro</label>
                    <textarea id="barnahus_new_event_excerpt" name="new_event[excerpt]" rows="3" placeholder="Short text shown on the event card"></textarea>
                </div>

                <div class="barnahus-event-dashboard-card__field barnahus-event-dashboard-card__full">
                    <div class="barnahus-event-dashboard-card__link-settings">
                        <div class="barnahus-event-dashboard-card__field">
                            <label for="barnahus_new_event_card_link_type">Card link destination</label>
                            <select id="barnahus_new_event_card_link_type" name="new_event[card_link_type]">
                                <?php foreach (barnahus_get_card_link_type_options() as $link_type_value => $link_type_label) : ?>
                                    <option value="<?php echo esc_attr($link_type_value); ?>" <?php selected('none', $link_type_value); ?>><?php echo esc_html($link_type_label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="barnahus-event-dashboard-card__field">
                            <label for="barnahus_new_event_registration_url">Registration URL</label>
                            <input type="url" id="barnahus_new_event_registration_url" name="new_event[registration_url]" placeholder="Registration URL">
                        </div>

                        <div class="barnahus-event-dashboard-card__field">
                            <label for="barnahus_new_event_custom_url">Manual card URL</label>
                            <input type="url" id="barnahus_new_event_custom_url" name="new_event[custom_url]" placeholder="Manual card URL">
                        </div>
                    </div>
                    <p class="barnahus-event-dashboard-card__link-note">Website event page keeps visitors on this site. Registration URL sends them straight to registration. Manual card URL uses the manual URL field.</p>
                </div>
            </div>

            <?php submit_button('Create event card', 'secondary', 'submit', false); ?>
        </form>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="barnahus_save_events_dashboard">
            <?php wp_nonce_field('barnahus_save_events_dashboard', 'barnahus_events_dashboard_nonce'); ?>

            <div class="barnahus-event-dashboard">
                <?php if (!$events) : ?>
                    <div class="barnahus-event-dashboard-card">No Barnahus events found.</div>
                <?php endif; ?>

                <?php foreach ($events as $event) : ?>
                    <?php
                    $post_id = $event->ID;
                    $date = get_post_meta($post_id, '_barnahus_event_date', true);
                    $start_time = get_post_meta($post_id, '_barnahus_event_start_time', true);
                    $end_time = get_post_meta($post_id, '_barnahus_event_end_time', true);
                    $location = get_post_meta($post_id, '_barnahus_event_location', true);
                    $registration_url = get_post_meta($post_id, '_barnahus_event_luma_url', true);
                    $custom_url = get_post_meta($post_id, '_barnahus_event_custom_url', true);
                    $card_link_type = barnahus_normalize_card_link_type(get_post_meta($post_id, '_barnahus_event_card_link_type', true));
                    $featured = get_post_meta($post_id, '_barnahus_event_featured', true) === '1';
                    $pinned = barnahus_is_event_pinned($post_id);
                    $hidden = get_post_meta($post_id, '_barnahus_event_hidden', true) === '1';
                    $registration_status = get_post_meta($post_id, '_barnahus_event_registration_status', true);
                    $series = barnahus_get_event_series_names($post_id);
                    $excerpt = get_the_excerpt($event);
                    ?>
                    <section class="barnahus-event-dashboard-card">
                        <div class="barnahus-event-dashboard-card__header">
                            <div>
                                <h2 class="barnahus-event-dashboard-card__title"><?php echo esc_html(get_the_title($event)); ?></h2>
                                <div class="barnahus-event-dashboard-card__meta">
                                    <?php echo esc_html(barnahus_format_event_meta($date, $start_time, $end_time, $location)); ?>
                                    <?php echo esc_html(' · ' . barnahus_get_event_dashboard_state($post_id, $event->post_status)); ?>
                                </div>
                            </div>
                            <div>
                                <a href="<?php echo esc_url(get_edit_post_link($post_id)); ?>">Edit page</a>
                                <?php if ('publish' === $event->post_status) : ?>
                                    <span> | </span><a href="<?php echo esc_url(get_permalink($event)); ?>">View</a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="barnahus-event-dashboard-card__fields">
                            <div class="barnahus-event-dashboard-card__field barnahus-event-dashboard-card__toggles">
                                <label>
                                    <input type="checkbox" name="events[<?php echo esc_attr($post_id); ?>][featured]" value="1" <?php checked($featured); ?>>
                                    Featured
                                </label>
                                <label>
                                    <input type="checkbox" name="events[<?php echo esc_attr($post_id); ?>][pinned]" value="1" <?php checked($pinned); ?>>
                                    Pinned to top
                                </label>
                                <label>
                                    <input type="checkbox" name="events[<?php echo esc_attr($post_id); ?>][hidden]" value="1" <?php checked($hidden); ?>>
                                    Hidden
                                </label>
                            </div>

                            <div class="barnahus-event-dashboard-card__field">
                                <label for="barnahus_event_series_<?php echo esc_attr($post_id); ?>">Event Series</label>
                                <input type="text" id="barnahus_event_series_<?php echo esc_attr($post_id); ?>" name="events[<?php echo esc_attr($post_id); ?>][series]" value="<?php echo esc_attr(implode(', ', $series)); ?>" placeholder="Webinars, Forum">
                            </div>

                            <div class="barnahus-event-dashboard-card__field">
                                <label for="barnahus_event_registration_status_<?php echo esc_attr($post_id); ?>">Registration status</label>
                                <select id="barnahus_event_registration_status_<?php echo esc_attr($post_id); ?>" name="events[<?php echo esc_attr($post_id); ?>][registration_status]">
                                    <?php foreach (barnahus_get_registration_status_options() as $status_value => $status_label) : ?>
                                        <option value="<?php echo esc_attr($status_value); ?>" <?php selected($registration_status, $status_value); ?>><?php echo esc_html($status_label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="barnahus-event-dashboard-card__field barnahus-event-dashboard-card__full">
                                <label for="barnahus_event_excerpt_<?php echo esc_attr($post_id); ?>">Card intro</label>
                                <textarea id="barnahus_event_excerpt_<?php echo esc_attr($post_id); ?>" name="events[<?php echo esc_attr($post_id); ?>][excerpt]" rows="4" placeholder="Short text shown on the event card"><?php echo esc_textarea($excerpt); ?></textarea>
                            </div>

                            <div class="barnahus-event-dashboard-card__field barnahus-event-dashboard-card__full">
                                <div class="barnahus-event-dashboard-card__link-settings">
                                    <div class="barnahus-event-dashboard-card__field">
                                        <label for="barnahus_event_card_link_type_<?php echo esc_attr($post_id); ?>">Card link destination</label>
                                        <select id="barnahus_event_card_link_type_<?php echo esc_attr($post_id); ?>" name="events[<?php echo esc_attr($post_id); ?>][card_link_type]">
                                            <?php foreach (barnahus_get_card_link_type_options() as $link_type_value => $link_type_label) : ?>
                                                <option value="<?php echo esc_attr($link_type_value); ?>" <?php selected($card_link_type, $link_type_value); ?>><?php echo esc_html($link_type_label); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="barnahus-event-dashboard-card__field">
                                        <label for="barnahus_event_registration_url_<?php echo esc_attr($post_id); ?>">Registration URL</label>
                                        <input type="url" id="barnahus_event_registration_url_<?php echo esc_attr($post_id); ?>" name="events[<?php echo esc_attr($post_id); ?>][registration_url]" value="<?php echo esc_url($registration_url); ?>" placeholder="Registration URL">
                                    </div>

                                    <div class="barnahus-event-dashboard-card__field">
                                        <label for="barnahus_event_custom_url_<?php echo esc_attr($post_id); ?>">Manual card URL</label>
                                        <input type="url" id="barnahus_event_custom_url_<?php echo esc_attr($post_id); ?>" name="events[<?php echo esc_attr($post_id); ?>][custom_url]" value="<?php echo esc_url($custom_url); ?>" placeholder="Manual card URL">
                                    </div>
                                </div>
                                <p class="barnahus-event-dashboard-card__link-note">Website event page keeps visitors on this site. Registration URL sends them straight to registration. Manual card URL uses the manual URL field.</p>
                            </div>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>

            <?php submit_button('Save event display settings'); ?>
        </form>
    </div>
    <?php
}

function barnahus_save_events_dashboard() {
    if (!current_user_can('edit_posts')) {
        wp_die('You do not have permission to edit events.');
    }

    if (!isset($_POST['barnahus_events_dashboard_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['barnahus_events_dashboard_nonce'])), 'barnahus_save_events_dashboard')) {
        wp_die('The event dashboard form could not be verified.');
    }

    $events = isset($_POST['events']) && is_array($_POST['events']) ? wp_unslash($_POST['events']) : array();

    barnahus_capture_event_dashboard_snapshot('Before dashboard save');

    foreach ($events as $post_id => $event_fields) {
        $post_id = absint($post_id);

        if (!$post_id || !current_user_can('edit_post', $post_id)) {
            continue;
        }

        if (!barnahus_is_event_post($post_id)) {
            continue;
        }

        update_post_meta($post_id, '_barnahus_event_featured', isset($event_fields['featured']) ? '1' : '0');
        update_post_meta($post_id, '_barnahus_event_pinned', isset($event_fields['pinned']) ? '1' : '0');
        update_post_meta($post_id, '_barnahus_event_hidden', isset($event_fields['hidden']) ? '1' : '0');
        update_post_meta($post_id, '_barnahus_event_luma_url', isset($event_fields['registration_url']) ? esc_url_raw($event_fields['registration_url']) : '');
        update_post_meta($post_id, '_barnahus_event_custom_url', isset($event_fields['custom_url']) ? esc_url_raw($event_fields['custom_url']) : '');
        update_post_meta($post_id, '_barnahus_event_card_link_type', isset($event_fields['card_link_type']) ? barnahus_normalize_card_link_type($event_fields['card_link_type']) : 'event-page');
        update_post_meta($post_id, '_barnahus_event_registration_status', isset($event_fields['registration_status']) ? barnahus_normalize_registration_status($event_fields['registration_status']) : '');

        $series_names = isset($event_fields['series']) ? barnahus_parse_event_series_names($event_fields['series']) : array();
        barnahus_set_event_series_names($post_id, $series_names);

        if (isset($event_fields['excerpt'])) {
            wp_update_post(
                array(
                    'ID' => $post_id,
                    'post_excerpt' => sanitize_textarea_field($event_fields['excerpt']),
                )
            );
        }
    }

    wp_safe_redirect(barnahus_get_events_dashboard_url(array('barnahus_events_saved' => '1')));
    exit;
}

function barnahus_create_event_from_dashboard() {
    if (!current_user_can('edit_posts')) {
        wp_die('You do not have permission to create events.');
    }

    if (!isset($_POST['barnahus_create_event_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['barnahus_create_event_nonce'])), 'barnahus_create_event_from_dashboard')) {
        wp_die('The event card form could not be verified.');
    }

    $event_fields = isset($_POST['new_event']) && is_array($_POST['new_event']) ? wp_unslash($_POST['new_event']) : array();
    $title = isset($event_fields['title']) ? sanitize_text_field($event_fields['title']) : '';

    if (!$title) {
        wp_safe_redirect(barnahus_get_events_dashboard_url(array('barnahus_event_error' => 'missing_title')));
        exit;
    }

    barnahus_capture_event_dashboard_snapshot('Before manual event card');

    $post_id = wp_insert_post(
        array(
            'post_type' => BARNAHUS_EVENT_CANONICAL_POST_TYPE,
            'post_status' => 'publish',
            'post_title' => $title,
            'post_excerpt' => isset($event_fields['excerpt']) ? sanitize_textarea_field($event_fields['excerpt']) : '',
            'post_content' => '',
        ),
        true
    );

    if (is_wp_error($post_id)) {
        wp_die(esc_html($post_id->get_error_message()));
    }

    update_post_meta($post_id, '_barnahus_event_date', isset($event_fields['date']) ? sanitize_text_field($event_fields['date']) : '');
    update_post_meta($post_id, '_barnahus_event_start_time', '');
    update_post_meta($post_id, '_barnahus_event_end_time', '');
    update_post_meta($post_id, '_barnahus_event_location', isset($event_fields['location']) ? sanitize_text_field($event_fields['location']) : '');
    update_post_meta($post_id, '_barnahus_event_luma_url', isset($event_fields['registration_url']) ? esc_url_raw($event_fields['registration_url']) : '');
    update_post_meta($post_id, '_barnahus_event_luma_embed_url', '');
    update_post_meta($post_id, '_barnahus_event_custom_url', isset($event_fields['custom_url']) ? esc_url_raw($event_fields['custom_url']) : '');
    update_post_meta($post_id, '_barnahus_event_card_link_type', isset($event_fields['card_link_type']) ? barnahus_normalize_card_link_type($event_fields['card_link_type']) : 'none');
    update_post_meta($post_id, '_barnahus_event_registration_status', isset($event_fields['registration_status']) ? barnahus_normalize_registration_status($event_fields['registration_status']) : 'coming-soon');
    update_post_meta($post_id, '_barnahus_event_featured', isset($event_fields['featured']) ? '1' : '0');
    update_post_meta($post_id, '_barnahus_event_pinned', isset($event_fields['pinned']) ? '1' : '0');
    update_post_meta($post_id, '_barnahus_event_hidden', '0');

    $series_names = isset($event_fields['series']) ? barnahus_parse_event_series_names($event_fields['series']) : array();
    barnahus_set_event_series_names($post_id, $series_names);

    wp_safe_redirect(barnahus_get_events_dashboard_url(array('barnahus_event_created' => '1')));
    exit;
}

function barnahus_refresh_luma_events_from_dashboard() {
    if (!current_user_can('edit_posts')) {
        wp_die('You do not have permission to refresh events.');
    }

    if (!isset($_POST['barnahus_refresh_luma_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['barnahus_refresh_luma_nonce'])), 'barnahus_refresh_luma_events')) {
        wp_die('The Luma refresh form could not be verified.');
    }

    $events = barnahus_fetch_luma_calendar_events(BARNAHUS_EVENT_LUMA_CALENDAR_URL);

    if (is_wp_error($events) || !$events) {
        wp_safe_redirect(barnahus_get_events_dashboard_url(array('barnahus_luma_error' => '1')));
        exit;
    }

    barnahus_capture_event_dashboard_snapshot('Before Luma refresh');

    $result = barnahus_import_luma_calendar_events($events);
    update_option('barnahus_event_luma_last_refresh', time());

    wp_safe_redirect(
        barnahus_get_events_dashboard_url(
            array(
                'barnahus_luma_refreshed' => '1',
                'created' => $result['created'],
                'updated' => $result['updated'],
                'skipped' => $result['skipped'],
            )
        )
    );
    exit;
}

function barnahus_fetch_luma_calendar_events($calendar_url) {
    $response = wp_remote_get(
        $calendar_url,
        array(
            'timeout' => 15,
            'headers' => array(
                'Accept' => 'text/html',
                'User-Agent' => 'Barnahus Site Tools; ' . home_url('/'),
            ),
        )
    );

    if (is_wp_error($response)) {
        return $response;
    }

    $body = wp_remote_retrieve_body($response);

    if (!$body) {
        return new WP_Error('barnahus_luma_empty_response', 'Luma returned an empty response.');
    }

    return barnahus_parse_luma_calendar_events($body);
}

function barnahus_parse_luma_calendar_events($html) {
    if (!preg_match_all('#<script[^>]*type=["\']application/ld\+json["\'][^>]*>(.*?)</script>#is', $html, $matches)) {
        return array();
    }

    $events = array();

    foreach ($matches[1] as $json) {
        $data = json_decode(html_entity_decode(trim($json), ENT_QUOTES | ENT_HTML5, 'UTF-8'), true);

        if (!is_array($data) || !isset($data['@type']) || 'ItemList' !== $data['@type'] || empty($data['itemListElement'])) {
            continue;
        }

        foreach ($data['itemListElement'] as $item) {
            if (empty($item['item']) || !is_array($item['item'])) {
                continue;
            }

            $event = barnahus_normalize_luma_schema_event($item['item']);

            if ($event) {
                $events[] = $event;
            }
        }
    }

    return $events;
}

function barnahus_normalize_luma_schema_event($event) {
    $url = isset($event['url']) ? esc_url_raw($event['url']) : '';
    $title = isset($event['name']) ? sanitize_text_field($event['name']) : '';

    if (!$url || !$title || empty($event['startDate'])) {
        return null;
    }

    $start = barnahus_parse_luma_datetime($event['startDate']);
    $end = !empty($event['endDate']) ? barnahus_parse_luma_datetime($event['endDate']) : null;

    if (!$start) {
        return null;
    }

    $series_names = array();

    if (!empty($event['organizer']) && is_array($event['organizer'])) {
        foreach ($event['organizer'] as $organizer) {
            if (!empty($organizer['name'])) {
                $series_names[] = sanitize_text_field($organizer['name']);
            }
        }
    }

    return array(
        'title' => $title,
        'url' => $url,
        'date' => $start['date'],
        'start_time' => $start['time'],
        'end_time' => $end ? $end['time'] : '',
        'location' => barnahus_normalize_luma_location(isset($event['location']) ? $event['location'] : null, isset($event['eventAttendanceMode']) ? $event['eventAttendanceMode'] : ''),
        'series' => $series_names,
        'category' => barnahus_guess_event_category_name($title, $series_names),
    );
}

function barnahus_parse_luma_datetime($datetime) {
    try {
        $date = new DateTime($datetime);
        $date->setTimezone(wp_timezone());
    } catch (Exception $exception) {
        return null;
    }

    return array(
        'date' => $date->format('Y-m-d'),
        'time' => $date->format('H:i'),
    );
}

function barnahus_normalize_luma_location($location, $attendance_mode = '') {
    if (is_array($location) && !empty($location['name'])) {
        return sanitize_text_field($location['name']);
    }

    if (is_string($location) && $location) {
        return sanitize_text_field($location);
    }

    if (false !== strpos($attendance_mode, 'OnlineEventAttendanceMode')) {
        return 'Online';
    }

    return 'To be announced';
}

function barnahus_import_luma_calendar_events($events) {
    $result = array(
        'created' => 0,
        'updated' => 0,
        'skipped' => 0,
    );

    foreach ($events as $event) {
        $post_id = barnahus_find_event_post_by_registration_url($event['url']);

        if ($post_id) {
            barnahus_update_event_from_luma($post_id, $event, false);
            $result['updated']++;
            continue;
        }

        $post_id = barnahus_create_event_post_from_luma($event);

        if (is_wp_error($post_id)) {
            $result['skipped']++;
            continue;
        }

        $result['created']++;
    }

    return $result;
}

function barnahus_find_event_post_by_registration_url($registration_url) {
    $matches = get_posts(
        array(
            'post_type' => barnahus_get_event_post_types(),
            'post_status' => array('publish', 'draft', 'pending', 'future'),
            'posts_per_page' => 1,
            'meta_key' => '_barnahus_event_luma_url',
            'meta_value' => esc_url_raw($registration_url),
            'fields' => 'ids',
            'no_found_rows' => true,
        )
    );

    return $matches ? absint($matches[0]) : 0;
}

function barnahus_create_event_post_from_luma($event) {
    $post_id = wp_insert_post(
        array(
            'post_type' => BARNAHUS_EVENT_CANONICAL_POST_TYPE,
            'post_status' => 'publish',
            'post_title' => $event['title'],
            'post_excerpt' => '',
            'post_content' => '',
        ),
        true
    );

    if (is_wp_error($post_id)) {
        return $post_id;
    }

    barnahus_update_event_from_luma($post_id, $event, true);

    return $post_id;
}

function barnahus_update_event_from_luma($post_id, $event, $is_new = false) {
    if ($is_new) {
        update_post_meta($post_id, '_barnahus_event_card_link_type', 'event-page');
        update_post_meta($post_id, '_barnahus_event_registration_status', 'open');
        update_post_meta($post_id, '_barnahus_event_featured', '0');
        update_post_meta($post_id, '_barnahus_event_pinned', '0');
        update_post_meta($post_id, '_barnahus_event_hidden', '0');

        if (!empty($event['category'])) {
            barnahus_assign_event_category($post_id, $event['category']);
        }
    }

    update_post_meta($post_id, '_barnahus_event_date', $event['date']);
    update_post_meta($post_id, '_barnahus_event_start_time', $event['start_time']);
    update_post_meta($post_id, '_barnahus_event_end_time', $event['end_time']);
    update_post_meta($post_id, '_barnahus_event_location', $event['location']);
    update_post_meta($post_id, '_barnahus_event_luma_url', $event['url']);
    update_post_meta($post_id, '_barnahus_event_luma_source', BARNAHUS_EVENT_LUMA_CALENDAR_URL);
    update_post_meta($post_id, '_barnahus_event_luma_last_seen', wp_date('c'));

    $existing_series = barnahus_get_event_series_names($post_id);

    if ($is_new || !$existing_series) {
        barnahus_set_event_series_names($post_id, $event['series']);
    }
}

function barnahus_guess_event_category_name($title, $series_names) {
    $haystack = strtolower($title . ' ' . implode(' ', (array) $series_names));

    if (false !== strpos($haystack, 'webinar')) {
        return 'Webinars';
    }

    if (false !== strpos($haystack, 'training')) {
        return 'Training';
    }

    if (false !== strpos($haystack, 'forum')) {
        return 'News';
    }

    return '';
}

function barnahus_assign_event_category($post_id, $category_name) {
    $category_name = sanitize_text_field($category_name);

    if (!$category_name || BARNAHUS_EVENT_CANONICAL_POST_TYPE !== get_post_type($post_id)) {
        return;
    }

    $category = get_term_by('name', $category_name, 'category');

    if (!$category) {
        $created = wp_insert_term($category_name, 'category');

        if (is_wp_error($created)) {
            return;
        }

        $category_id = absint($created['term_id']);
    } else {
        $category_id = absint($category->term_id);
    }

    wp_set_post_categories($post_id, array($category_id), false);
}

function barnahus_convert_event_pages_to_posts_from_dashboard() {
    if (!current_user_can('edit_posts')) {
        wp_die('You do not have permission to convert event pages.');
    }

    if (!isset($_POST['barnahus_convert_events_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['barnahus_convert_events_nonce'])), 'barnahus_convert_event_pages_to_posts')) {
        wp_die('The event conversion form could not be verified.');
    }

    barnahus_capture_event_dashboard_snapshot('Before event page conversion');

    $converted = barnahus_convert_legacy_event_pages_to_posts();

    wp_safe_redirect(barnahus_get_events_dashboard_url(array('barnahus_events_converted' => '1', 'converted' => $converted)));
    exit;
}

function barnahus_convert_legacy_event_pages_to_posts() {
    $post_ids = barnahus_get_legacy_event_posts();
    $converted = 0;

    foreach ($post_ids as $post_id) {
        $post_id = absint($post_id);
        $series_names = barnahus_get_event_series_names($post_id);

        $updated = wp_update_post(
            array(
                'ID' => $post_id,
                'post_type' => BARNAHUS_EVENT_CANONICAL_POST_TYPE,
            ),
            true
        );

        if (is_wp_error($updated)) {
            continue;
        }

        barnahus_set_event_series_names($post_id, $series_names);
        $converted++;
    }

    return $converted;
}

function barnahus_get_event_series_names($post_id) {
    if (BARNAHUS_EVENT_CANONICAL_POST_TYPE === get_post_type($post_id)) {
        $terms = get_the_terms($post_id, 'post_tag');

        if (!$terms || is_wp_error($terms)) {
            return array();
        }

        $names = array();

        foreach ($terms as $term) {
            if (BARNAHUS_EVENT_TAG_SLUG === $term->slug) {
                continue;
            }

            $names[] = $term->name;
        }

        return $names;
    }

    $terms = get_the_terms($post_id, BARNAHUS_EVENT_SERIES_TAXONOMY);

    if (!$terms || is_wp_error($terms)) {
        return array();
    }

    return wp_list_pluck($terms, 'name');
}

function barnahus_parse_event_series_names($series) {
    $series = is_string($series) ? $series : '';
    $names = array_map('trim', explode(',', $series));
    $names = array_filter($names);

    return array_values(array_unique($names));
}

function barnahus_set_event_series_names($post_id, $series_names) {
    $series_names = array_values(array_filter(array_map('sanitize_text_field', (array) $series_names)));

    if (BARNAHUS_EVENT_CANONICAL_POST_TYPE === get_post_type($post_id)) {
        $tag_names = array_merge(array('event'), $series_names);
        wp_set_object_terms($post_id, $tag_names, 'post_tag', false);
        return;
    }

    wp_set_object_terms($post_id, $series_names, BARNAHUS_EVENT_SERIES_TAXONOMY, false);
}

function barnahus_get_event_dashboard_state($post_id, $post_status) {
    if ('publish' !== $post_status) {
        return ucfirst($post_status);
    }

    if (get_post_meta($post_id, '_barnahus_event_hidden', true) === '1') {
        return 'Hidden';
    }

    if (barnahus_is_event_pinned($post_id)) {
        return 'Pinned';
    }

    $date = get_post_meta($post_id, '_barnahus_event_date', true);

    if ($date && $date < wp_date('Y-m-d')) {
        return 'Past';
    }

    if (get_post_meta($post_id, '_barnahus_event_featured', true) === '1') {
        return 'Featured';
    }

    return 'Public';
}

function barnahus_get_registration_status_options() {
    return array(
        '' => 'No status',
        'open' => 'Registration open',
        'closed' => 'Closed',
        'waitlist' => 'Waitlist',
        'coming-soon' => 'Coming soon',
        'member-only' => 'Member only',
    );
}

function barnahus_migrate_legacy_registration_status($status) {
    if ('wait-list' === $status) {
        return 'waitlist';
    }

    return $status;
}

function barnahus_normalize_registration_status($status) {
    $status = barnahus_migrate_legacy_registration_status(sanitize_key($status));
    $allowed_statuses = array_keys(barnahus_get_registration_status_options());

    if (!in_array($status, $allowed_statuses, true)) {
        return '';
    }

    return $status;
}

function barnahus_get_registration_status_label($status) {
    $status = barnahus_normalize_registration_status($status);

    if (!$status) {
        return '';
    }

    $options = barnahus_get_registration_status_options();

    return isset($options[$status]) ? $options[$status] : '';
}

function barnahus_get_card_link_type_options() {
    return array(
        'none' => 'No card link',
        'event-page' => 'Website event page',
        'registration' => 'Registration URL (direct)',
        'custom' => 'Manual card URL',
    );
}

function barnahus_normalize_card_link_type($link_type) {
    $link_type = sanitize_key($link_type);
    $allowed_link_types = array_keys(barnahus_get_card_link_type_options());

    if (!$link_type) {
        return 'event-page';
    }

    if (!in_array($link_type, $allowed_link_types, true)) {
        return 'event-page';
    }

    return $link_type;
}

function barnahus_add_event_details_meta_box($post_type, $post = null) {
    if (BARNAHUS_EVENT_POST_TYPE !== $post_type && (!$post || !barnahus_is_event_post($post->ID))) {
        return;
    }

    add_meta_box(
        'barnahus-event-details',
        'Event Details',
        'barnahus_render_event_details_meta_box',
        $post_type,
        'normal',
        'high'
    );
}

function barnahus_add_event_usage_meta_box($post_type, $post = null) {
    if (BARNAHUS_EVENT_POST_TYPE !== $post_type && (!$post || !barnahus_is_event_post($post->ID))) {
        return;
    }

    add_meta_box(
        'barnahus-event-usage',
        'Display Shortcode',
        'barnahus_render_event_usage_meta_box',
        $post_type,
        'side',
        'default'
    );
}

function barnahus_render_event_usage_meta_box($post) {
    ?>
    <p>Single card:</p>
    <p><code>[barnahus_event_card id="<?php echo esc_attr($post->ID); ?>"]</code></p>
    <p>Compact single card:</p>
    <p><code>[barnahus_event_card id="<?php echo esc_attr($post->ID); ?>" compact="true"]</code></p>
    <?php
}

function barnahus_render_event_details_meta_box($post) {
    wp_nonce_field('barnahus_save_event_details', 'barnahus_event_details_nonce');

    $event_date = get_post_meta($post->ID, '_barnahus_event_date', true);
    $start_time = get_post_meta($post->ID, '_barnahus_event_start_time', true);
    $end_time = get_post_meta($post->ID, '_barnahus_event_end_time', true);
    $location = get_post_meta($post->ID, '_barnahus_event_location', true);
    $luma_url = get_post_meta($post->ID, '_barnahus_event_luma_url', true);
    $luma_embed_url = get_post_meta($post->ID, '_barnahus_event_luma_embed_url', true);
    $custom_url = get_post_meta($post->ID, '_barnahus_event_custom_url', true);
    $card_link_type = barnahus_normalize_card_link_type(get_post_meta($post->ID, '_barnahus_event_card_link_type', true));
    $button_label = get_post_meta($post->ID, '_barnahus_event_button_label', true);
    $registration_status = get_post_meta($post->ID, '_barnahus_event_registration_status', true);
    $featured = get_post_meta($post->ID, '_barnahus_event_featured', true);
    $pinned = barnahus_is_event_pinned($post->ID);
    $hidden = get_post_meta($post->ID, '_barnahus_event_hidden', true);
    ?>
    <style>
        .barnahus-event-fields {
            display: grid;
            gap: 16px;
            max-width: 760px;
        }

        .barnahus-event-field label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .barnahus-event-field input[type="text"],
        .barnahus-event-field input[type="url"],
        .barnahus-event-field input[type="date"],
        .barnahus-event-field input[type="time"] {
            width: 100%;
        }

        .barnahus-event-field-row {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }
    </style>

    <div class="barnahus-event-fields">
        <div class="barnahus-event-field-row">
            <div class="barnahus-event-field">
                <label for="barnahus_event_date">Date</label>
                <input type="date" id="barnahus_event_date" name="barnahus_event_date" value="<?php echo esc_attr($event_date); ?>">
            </div>

            <div class="barnahus-event-field">
                <label for="barnahus_event_start_time">Start time</label>
                <input type="time" id="barnahus_event_start_time" name="barnahus_event_start_time" value="<?php echo esc_attr($start_time); ?>">
            </div>

            <div class="barnahus-event-field">
                <label for="barnahus_event_end_time">End time</label>
                <input type="time" id="barnahus_event_end_time" name="barnahus_event_end_time" value="<?php echo esc_attr($end_time); ?>">
            </div>
        </div>

        <div class="barnahus-event-field">
            <label for="barnahus_event_location">Location / platform</label>
            <input type="text" id="barnahus_event_location" name="barnahus_event_location" value="<?php echo esc_attr($location); ?>">
        </div>

        <div class="barnahus-event-field">
            <label for="barnahus_event_card_link_type">Card link destination</label>
            <select id="barnahus_event_card_link_type" name="barnahus_event_card_link_type">
                <?php foreach (barnahus_get_card_link_type_options() as $link_type_value => $link_type_label) : ?>
                    <option value="<?php echo esc_attr($link_type_value); ?>" <?php selected($card_link_type, $link_type_value); ?>><?php echo esc_html($link_type_label); ?></option>
                <?php endforeach; ?>
            </select>
            <p class="description">Website event page keeps visitors on this site. Registration URL sends them straight to registration. Manual card URL uses the manual URL field.</p>
        </div>

        <div class="barnahus-event-field">
            <label for="barnahus_event_luma_url">Registration URL</label>
            <input type="url" id="barnahus_event_luma_url" name="barnahus_event_luma_url" value="<?php echo esc_url($luma_url); ?>">
        </div>

        <div class="barnahus-event-field">
            <label for="barnahus_event_luma_embed_url">Embedded registration URL override</label>
            <input type="url" id="barnahus_event_luma_embed_url" name="barnahus_event_luma_embed_url" value="<?php echo esc_url($luma_embed_url); ?>">
            <p class="description">Usually leave blank. Event pages use the Registration URL automatically when it can be embedded; add an override only if a registration service gives you a separate iframe URL.</p>
        </div>

        <div class="barnahus-event-field">
            <label for="barnahus_event_custom_url">Manual card URL</label>
            <input type="url" id="barnahus_event_custom_url" name="barnahus_event_custom_url" value="<?php echo esc_url($custom_url); ?>">
            <p class="description">Optional. Used only when the card link destination is Manual card URL.</p>
        </div>

        <div class="barnahus-event-field">
            <label for="barnahus_event_button_label">Button label</label>
            <input type="text" id="barnahus_event_button_label" name="barnahus_event_button_label" value="<?php echo esc_attr($button_label); ?>" placeholder="Read more">
        </div>

        <div class="barnahus-event-field">
            <label for="barnahus_event_registration_status">Registration status</label>
            <select id="barnahus_event_registration_status" name="barnahus_event_registration_status">
                <?php foreach (barnahus_get_registration_status_options() as $status_value => $status_label) : ?>
                    <option value="<?php echo esc_attr($status_value); ?>" <?php selected($registration_status, $status_value); ?>><?php echo esc_html($status_label); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="barnahus-event-field">
            <label>
                <input type="checkbox" name="barnahus_event_featured" value="1" <?php checked($featured, '1'); ?>>
                Featured: show this event as a larger highlighted card
            </label>
        </div>

        <div class="barnahus-event-field">
            <label>
                <input type="checkbox" name="barnahus_event_pinned" value="1" <?php checked($pinned); ?>>
                Pinned to top: show this event before chronological events
            </label>
        </div>

        <div class="barnahus-event-field">
            <label>
                <input type="checkbox" name="barnahus_event_hidden" value="1" <?php checked($hidden, '1'); ?>>
                Hidden: do not show this event in the public grid
            </label>
        </div>
    </div>
    <?php
}

function barnahus_save_event_details($post_id) {
    if (!isset($_POST['barnahus_event_details_nonce'])) {
        return;
    }

    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['barnahus_event_details_nonce'])), 'barnahus_save_event_details')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (!barnahus_is_event_post($post_id)) {
        return;
    }

    $fields = array(
        '_barnahus_event_date' => array('barnahus_event_date', 'sanitize_text_field'),
        '_barnahus_event_start_time' => array('barnahus_event_start_time', 'sanitize_text_field'),
        '_barnahus_event_end_time' => array('barnahus_event_end_time', 'sanitize_text_field'),
        '_barnahus_event_location' => array('barnahus_event_location', 'sanitize_text_field'),
        '_barnahus_event_luma_url' => array('barnahus_event_luma_url', 'esc_url_raw'),
        '_barnahus_event_luma_embed_url' => array('barnahus_event_luma_embed_url', 'esc_url_raw'),
        '_barnahus_event_custom_url' => array('barnahus_event_custom_url', 'esc_url_raw'),
        '_barnahus_event_card_link_type' => array('barnahus_event_card_link_type', 'barnahus_normalize_card_link_type'),
        '_barnahus_event_button_label' => array('barnahus_event_button_label', 'sanitize_text_field'),
        '_barnahus_event_registration_status' => array('barnahus_event_registration_status', 'barnahus_normalize_registration_status'),
    );

    foreach ($fields as $meta_key => $field_config) {
        list($field_name, $sanitize_callback) = $field_config;
        $value = isset($_POST[$field_name]) ? call_user_func($sanitize_callback, wp_unslash($_POST[$field_name])) : '';
        update_post_meta($post_id, $meta_key, $value);
    }

    update_post_meta($post_id, '_barnahus_event_featured', isset($_POST['barnahus_event_featured']) ? '1' : '0');
    update_post_meta($post_id, '_barnahus_event_pinned', isset($_POST['barnahus_event_pinned']) ? '1' : '0');
    update_post_meta($post_id, '_barnahus_event_hidden', isset($_POST['barnahus_event_hidden']) ? '1' : '0');
}

function barnahus_events_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'limit' => 12,
            'show_past' => 'false',
            'columns' => 'auto',
            'min_width' => 360,
            'featured' => 'first',
            'featured_order' => 'pinned',
            'time' => '',
            'series' => '',
            'compact' => 'false',
            'variant' => 'quiet',
            'description_words' => 22,
            'show_description' => 'true',
            'empty_message' => 'No upcoming events are currently listed.',
        ),
        $atts,
        'barnahus_events'
    );

    $limit = max(1, absint($atts['limit']));
    $show_past = filter_var($atts['show_past'], FILTER_VALIDATE_BOOLEAN);
    $compact = filter_var($atts['compact'], FILTER_VALIDATE_BOOLEAN);
    $columns = barnahus_normalize_event_columns($atts['columns']);
    $min_width = barnahus_normalize_event_min_width($atts['min_width']);
    $featured_mode = barnahus_normalize_featured_mode($atts['featured']);
    $featured_order = barnahus_normalize_featured_order($atts['featured_order']);
    $event_time = barnahus_normalize_event_time($atts['time'], $show_past);
    $series = sanitize_title($atts['series']);
    $variant = barnahus_normalize_event_variant($atts['variant']);
    $description_words = barnahus_normalize_description_words($atts['description_words']);
    $show_description = filter_var($atts['show_description'], FILTER_VALIDATE_BOOLEAN);

    $events = barnahus_get_events_for_display($event_time, $featured_mode, $series, $featured_order);

    if (!$events) {
        return '<p class="bh-events-empty">' . esc_html($atts['empty_message']) . '</p>';
    }

    $events = array_slice($events, 0, $limit);

    barnahus_enqueue_events_assets();

    ob_start();
    ?>
    <div class="<?php echo esc_attr(barnahus_get_events_grid_classes($columns, $compact, $variant)); ?>" style="<?php echo esc_attr(barnahus_get_events_grid_style($columns, $min_width)); ?>">
        <?php foreach ($events as $event) : ?>
            <?php echo barnahus_render_event_card($event, array('compact' => $compact, 'variant' => $variant, 'description_words' => $description_words, 'show_description' => $show_description)); ?>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

function barnahus_event_card_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'id' => 0,
            'compact' => 'false',
            'variant' => 'quiet',
            'description_words' => 22,
            'show_description' => 'true',
        ),
        $atts,
        'barnahus_event_card'
    );

    $event_id = absint($atts['id']);
    $compact = filter_var($atts['compact'], FILTER_VALIDATE_BOOLEAN);
    $variant = barnahus_normalize_event_variant($atts['variant']);
    $description_words = barnahus_normalize_description_words($atts['description_words']);
    $show_description = filter_var($atts['show_description'], FILTER_VALIDATE_BOOLEAN);

    if (!$event_id) {
        return '';
    }

    $event = get_post($event_id);

    if (!$event || !barnahus_is_event_post($event->ID) || 'publish' !== $event->post_status) {
        return '';
    }

    if (get_post_meta($event->ID, '_barnahus_event_hidden', true) === '1') {
        return '';
    }

    barnahus_enqueue_events_assets();

    return barnahus_render_event_card($event, array('compact' => $compact, 'variant' => $variant, 'description_words' => $description_words, 'show_description' => $show_description));
}

function barnahus_get_events_for_display($event_time = 'upcoming', $featured_mode = 'first', $series = '', $featured_order = 'pinned') {
    $meta_query = array(
        'relation' => 'AND',
        array(
            'relation' => 'OR',
            array(
                'key' => '_barnahus_event_hidden',
                'value' => '1',
                'compare' => '!=',
            ),
            array(
                'key' => '_barnahus_event_hidden',
                'compare' => 'NOT EXISTS',
            ),
        ),
    );

    if ('upcoming' === $event_time) {
        $meta_query[] = array(
            'relation' => 'OR',
            array(
                'key' => '_barnahus_event_date',
                'value' => wp_date('Y-m-d'),
                'compare' => '>=',
                'type' => 'DATE',
            ),
            array(
                'key' => '_barnahus_event_date',
                'value' => '',
                'compare' => '=',
            ),
            array(
                'key' => '_barnahus_event_date',
                'compare' => 'NOT EXISTS',
            ),
        );
    } elseif ('past' === $event_time) {
        $meta_query[] = array(
            'key' => '_barnahus_event_date',
            'value' => wp_date('Y-m-d'),
            'compare' => '<',
            'type' => 'DATE',
        );
    }

    if ('only' === $featured_mode) {
        $meta_query[] = array(
            'key' => '_barnahus_event_featured',
            'value' => '1',
            'compare' => '=',
        );
    } elseif ('exclude' === $featured_mode) {
        $meta_query[] = array(
            'key' => '_barnahus_event_featured',
            'value' => '1',
            'compare' => '!=',
        );
    }

    $query_args = array(
        'post_type' => barnahus_get_event_post_types(),
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => $meta_query,
        'no_found_rows' => true,
    );

    $events = get_posts(
        $query_args
    );

    $events = array_filter(
        $events,
        function ($event) use ($series) {
            if (!barnahus_event_post_is_dashboard_event($event)) {
                return false;
            }

            if (!$series) {
                return true;
            }

            $series_slugs = array_map('sanitize_title', barnahus_get_event_series_names($event->ID));

            return in_array($series, $series_slugs, true);
        }
    );

    usort(
        $events,
        function ($event_a, $event_b) use ($featured_order, $event_time) {
            $comparison = barnahus_compare_events_for_display($event_a, $event_b, $featured_order);

            if ('past' === $event_time) {
                return $comparison * -1;
            }

            return $comparison;
        }
    );

    return $events;
}

function barnahus_normalize_event_time($time, $show_past = false) {
    $time = sanitize_key($time);

    if (in_array($time, array('upcoming', 'past', 'all'), true)) {
        return $time;
    }

    return $show_past ? 'all' : 'upcoming';
}

function barnahus_compare_events_for_display($event_a, $event_b, $featured_order = 'pinned') {
    if ('pinned' === $featured_order) {
        $a_pinned = barnahus_is_event_pinned($event_a->ID);
        $b_pinned = barnahus_is_event_pinned($event_b->ID);

        if ($a_pinned !== $b_pinned) {
            return $a_pinned ? -1 : 1;
        }
    }

    $a_datetime = barnahus_get_event_sort_datetime($event_a->ID);
    $b_datetime = barnahus_get_event_sort_datetime($event_b->ID);

    return strcmp($a_datetime, $b_datetime);
}

function barnahus_get_event_sort_datetime($post_id) {
    $date = get_post_meta($post_id, '_barnahus_event_date', true);
    $time = get_post_meta($post_id, '_barnahus_event_start_time', true);

    if (!$date) {
        $date = '9999-12-31';
    }

    if (!$time) {
        $time = '00:00';
    }

    return $date . ' ' . $time;
}

function barnahus_is_event_pinned($post_id) {
    if (metadata_exists('post', $post_id, '_barnahus_event_pinned')) {
        return get_post_meta($post_id, '_barnahus_event_pinned', true) === '1';
    }

    return get_post_meta($post_id, '_barnahus_event_featured', true) === '1';
}

function barnahus_render_event_card($event, $args = array()) {
    $args = wp_parse_args(
        $args,
        array(
            'compact' => false,
            'variant' => 'quiet',
            'description_words' => 22,
            'show_description' => true,
        )
    );

    $date = get_post_meta($event->ID, '_barnahus_event_date', true);
    $start_time = get_post_meta($event->ID, '_barnahus_event_start_time', true);
    $end_time = get_post_meta($event->ID, '_barnahus_event_end_time', true);
    $location = get_post_meta($event->ID, '_barnahus_event_location', true);
    $featured = get_post_meta($event->ID, '_barnahus_event_featured', true) === '1';
    $registration_status_label = barnahus_get_registration_status_label(get_post_meta($event->ID, '_barnahus_event_registration_status', true));
    $series_names = barnahus_get_event_series_names($event->ID);
    $visible_series_names = array_slice($series_names, 0, $featured ? 2 : 1);
    $timestamp = $date ? strtotime($date . ' ' . ($start_time ? $start_time : '00:00')) : false;
    $description = has_excerpt($event->ID)
        ? get_the_excerpt($event)
        : wp_trim_words(wp_strip_all_tags($event->post_content), $args['description_words']);

    $description = wp_trim_words(wp_strip_all_tags($description), $args['description_words']);

    ob_start();
    ?>
    <article class="<?php echo esc_attr(barnahus_get_event_card_classes($featured, $args['compact'], $args['variant'])); ?>" aria-labelledby="bh-event-title-<?php echo esc_attr($event->ID); ?>">
        <time class="bh-event-date" datetime="<?php echo esc_attr($date); ?>">
            <span class="bh-event-day"><?php echo esc_html($timestamp ? date_i18n('j', $timestamp) : 'TBA'); ?></span>
            <span class="bh-event-month"><?php echo esc_html($timestamp ? date_i18n('M', $timestamp) : ''); ?></span>
        </time>

        <div class="bh-event-content">
            <div class="bh-event-tags">
                <?php if ($visible_series_names) : ?>
                    <?php foreach ($visible_series_names as $series_name) : ?>
                        <span class="bh-event-tag"><?php echo esc_html($series_name); ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($registration_status_label) : ?>
                    <span class="bh-event-tag bh-event-tag--status"><?php echo esc_html($registration_status_label); ?></span>
                <?php endif; ?>
            </div>

            <h3 class="bh-event-title" id="bh-event-title-<?php echo esc_attr($event->ID); ?>"><?php echo esc_html(get_the_title($event)); ?></h3>

            <p class="bh-event-meta">
                <?php echo esc_html(barnahus_format_event_meta($date, $start_time, $end_time, $location)); ?>
            </p>

            <?php if ($args['show_description'] && !$args['compact'] && $description) : ?>
                <p class="bh-event-description"><?php echo esc_html($description); ?></p>
            <?php endif; ?>

            <?php $card_link = barnahus_get_event_card_link($event); ?>
            <?php if ($card_link) : ?>
                <a class="bh-event-link" href="<?php echo esc_url($card_link['url']); ?>"><?php echo esc_html($card_link['label']); ?></a>
            <?php endif; ?>
        </div>
    </article>
    <?php
    return ob_get_clean();
}

function barnahus_get_event_card_link($event) {
    $link_type = barnahus_normalize_card_link_type(get_post_meta($event->ID, '_barnahus_event_card_link_type', true));
    $registration_url = get_post_meta($event->ID, '_barnahus_event_luma_url', true);
    $custom_url = get_post_meta($event->ID, '_barnahus_event_custom_url', true);

    if ('none' === $link_type) {
        return null;
    }

    if ('registration' === $link_type && $registration_url) {
        return array(
            'url' => $registration_url,
            'label' => 'Register',
        );
    }

    if ('custom' === $link_type && $custom_url) {
        return array(
            'url' => $custom_url,
            'label' => 'Read more',
        );
    }

    return array(
        'url' => get_permalink($event),
        'label' => 'Read more',
    );
}

function barnahus_get_event_registration_embed_url($post_id) {
    $embed_url = get_post_meta($post_id, '_barnahus_event_luma_embed_url', true);

    if ($embed_url) {
        return $embed_url;
    }

    $registration_url = get_post_meta($post_id, '_barnahus_event_luma_url', true);

    if ($registration_url && preg_match('#^https?://([^/]+\.)?luma\.com/#i', $registration_url)) {
        return $registration_url;
    }

    return '';
}

function barnahus_render_event_single_content($content) {
    if (is_admin() || !is_singular(barnahus_get_event_post_types()) || !in_the_loop() || !is_main_query()) {
        return $content;
    }

    if (!barnahus_is_event_post(get_the_ID())) {
        return $content;
    }

    barnahus_enqueue_events_assets();

    $post_id = get_the_ID();
    $date = get_post_meta($post_id, '_barnahus_event_date', true);
    $start_time = get_post_meta($post_id, '_barnahus_event_start_time', true);
    $end_time = get_post_meta($post_id, '_barnahus_event_end_time', true);
    $location = get_post_meta($post_id, '_barnahus_event_location', true);
    $luma_url = get_post_meta($post_id, '_barnahus_event_luma_url', true);
    $registration_embed_url = barnahus_get_event_registration_embed_url($post_id);
    $meta = barnahus_format_event_meta($date, $start_time, $end_time, $location);

    ob_start();
    ?>
    <section class="bh-event-single">
        <?php if ($meta) : ?>
            <p class="bh-event-single__meta"><?php echo esc_html($meta); ?></p>
        <?php endif; ?>

        <div class="bh-event-single__content">
            <?php echo $content; ?>
        </div>

        <?php if ($registration_embed_url) : ?>
            <div class="bh-event-single__embed">
                <iframe src="<?php echo esc_url($registration_embed_url); ?>" loading="lazy" allowfullscreen="yes" aria-label="<?php echo esc_attr(sprintf('Registration for %s', get_the_title($post_id))); ?>"></iframe>
            </div>
        <?php endif; ?>

        <?php if ($luma_url) : ?>
            <p class="bh-event-single__action">
                <a class="bh-event-link" href="<?php echo esc_url($luma_url); ?>">Register</a>
            </p>
        <?php endif; ?>
    </section>
    <?php
    return ob_get_clean();
}

function barnahus_format_event_meta($date, $start_time, $end_time, $location) {
    $parts = array();
    $timestamp = $date ? strtotime($date . ' ' . ($start_time ? $start_time : '00:00')) : false;

    if ($timestamp) {
        $parts[] = date_i18n('l j F', $timestamp);
    }

    if ($start_time && $end_time) {
        $parts[] = $start_time . '-' . $end_time;
    } elseif ($start_time) {
        $parts[] = $start_time;
    }

    if ($location) {
        $parts[] = $location;
    }

    return implode(' · ', $parts);
}

function barnahus_enqueue_events_assets() {
    wp_enqueue_style(
        'barnahus-events',
        plugin_dir_url(dirname(__FILE__)) . 'css/events.css',
        array(),
        '1.0.0'
    );

    wp_enqueue_script(
        'barnahus-events',
        plugin_dir_url(dirname(__FILE__)) . 'js/events.js',
        array(),
        '1.0.0',
        true
    );
}

function barnahus_normalize_event_columns($columns) {
    if ('auto' === $columns) {
        return 'auto';
    }

    $columns = absint($columns);

    if ($columns < 1 || $columns > 4) {
        return 'auto';
    }

    return (string) $columns;
}

function barnahus_normalize_event_min_width($min_width) {
    $min_width = absint($min_width);

    if ($min_width < 360) {
        return 360;
    }

    if ($min_width > 460) {
        return 460;
    }

    return $min_width;
}

function barnahus_normalize_featured_mode($featured_mode) {
    $allowed_modes = array('first', 'include', 'only', 'exclude');

    if (!in_array($featured_mode, $allowed_modes, true)) {
        return 'first';
    }

    return $featured_mode;
}

function barnahus_normalize_featured_order($featured_order) {
    $allowed_orders = array('pinned', 'chronological');

    if (!in_array($featured_order, $allowed_orders, true)) {
        return 'pinned';
    }

    return $featured_order;
}

function barnahus_normalize_event_variant($variant) {
    $allowed_variants = array('standard', 'quiet', 'plain');

    if (!in_array($variant, $allowed_variants, true)) {
        return 'standard';
    }

    return $variant;
}

function barnahus_normalize_description_words($word_count) {
    $word_count = absint($word_count);

    if ($word_count < 8) {
        return 8;
    }

    if ($word_count > 40) {
        return 40;
    }

    return $word_count;
}

function barnahus_get_events_grid_classes($columns, $compact, $variant = 'standard') {
    $classes = array('bh-events-grid');

    if ('auto' !== $columns) {
        $classes[] = 'bh-events-grid--columns-' . $columns;
    }

    if ($compact) {
        $classes[] = 'bh-events-grid--compact';
    }

    $classes[] = 'bh-events-grid--' . barnahus_normalize_event_variant($variant);

    return implode(' ', $classes);
}

function barnahus_get_events_grid_style($columns, $min_width) {
    if ('auto' !== $columns) {
        return '';
    }

    return '--bh-event-card-min-width: ' . absint($min_width) . 'px;';
}

function barnahus_get_event_card_classes($featured, $compact, $variant = 'standard') {
    $classes = array('bh-event-card');

    if ($featured) {
        $classes[] = 'is-featured';
    }

    if ($compact) {
        $classes[] = 'is-compact';
    }

    $classes[] = 'bh-event-card--' . barnahus_normalize_event_variant($variant);

    return implode(' ', $classes);
}

function barnahus_event_admin_columns($columns) {
    $new_columns = array();

    foreach ($columns as $key => $label) {
        $new_columns[$key] = $label;

        if ('title' === $key) {
            $new_columns['barnahus_event_date'] = 'Event date';
            $new_columns['barnahus_event_status'] = 'Display';
        }
    }

    return $new_columns;
}

function barnahus_event_admin_column_content($column, $post_id) {
    if ('barnahus_event_date' === $column) {
        echo esc_html(barnahus_format_event_meta(
            get_post_meta($post_id, '_barnahus_event_date', true),
            get_post_meta($post_id, '_barnahus_event_start_time', true),
            get_post_meta($post_id, '_barnahus_event_end_time', true),
            get_post_meta($post_id, '_barnahus_event_location', true)
        ));
    }

    if ('barnahus_event_status' === $column) {
        $labels = array();

        if (get_post_meta($post_id, '_barnahus_event_featured', true) === '1') {
            $labels[] = 'Featured';
        }

        if (barnahus_is_event_pinned($post_id)) {
            $labels[] = 'Pinned';
        }

        if (get_post_meta($post_id, '_barnahus_event_hidden', true) === '1') {
            $labels[] = 'Hidden';
        }

        echo esc_html($labels ? implode(', ', $labels) : 'Public');
    }
}

function barnahus_event_sortable_admin_columns($columns) {
    $columns['barnahus_event_date'] = 'barnahus_event_date';

    return $columns;
}

function barnahus_event_admin_orderby($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    if (BARNAHUS_EVENT_POST_TYPE !== $query->get('post_type')) {
        return;
    }

    if ('barnahus_event_date' !== $query->get('orderby')) {
        return;
    }

    $query->set('meta_key', '_barnahus_event_date');
    $query->set('orderby', 'meta_value');
}
