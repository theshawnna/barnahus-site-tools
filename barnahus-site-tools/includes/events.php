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
add_action('admin_post_barnahus_create_event_post_page', 'barnahus_create_event_post_page_from_dashboard');
add_action('admin_post_barnahus_unarchive_event', 'barnahus_unarchive_event_from_dashboard');
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
        '_barnahus_event_archived',
        '_barnahus_event_hidden',
        '_barnahus_event_hide_date',
        '_barnahus_event_linked_post_id',
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
    $active_events = array_values(array_filter($events, function ($event) {
        return !barnahus_event_is_archived($event->ID);
    }));
    $archived_events = array_values(array_filter($events, function ($event) {
        return barnahus_event_is_archived($event->ID);
    }));
    $dashboard_events = array_merge($active_events, $archived_events);

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

        <?php if (isset($_GET['barnahus_event_post_created'])) : ?>
            <div class="notice notice-success is-dismissible">
                <p>Automatic event post created as a draft.</p>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['barnahus_event_restored'])) : ?>
            <div class="notice notice-success is-dismissible">
                <p>Event dashboard history restored for <?php echo esc_html(absint($_GET['restored'])); ?> event(s).</p>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['barnahus_event_unarchived'])) : ?>
            <div class="notice notice-success is-dismissible">
                <p>Event moved back to the active dashboard list.</p>
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

            .barnahus-event-dashboard-tip {
                margin: 0 0 16px;
                border-left: 4px solid #aeb9ee;
                background: #fff;
                padding: 10px 12px;
            }

            .barnahus-event-dashboard-tip p {
                margin: 0;
            }

            .barnahus-event-dashboard-create {
                margin: 16px 0;
                border-left: 4px solid #aeb9ee;
            }

            .barnahus-event-dashboard-create summary {
                cursor: pointer;
                font-weight: 600;
            }

            .barnahus-event-dashboard-create form {
                margin-top: 14px;
            }

            .barnahus-event-dashboard-history {
                margin: 0 0 16px;
                background: #fff;
                border: 1px solid #c3c4c7;
                padding: 12px 16px;
            }

            .barnahus-event-dashboard-archive {
                margin-top: 18px;
                background: #f6f7f7;
                border: 1px solid #c3c4c7;
                padding: 12px;
            }

            .barnahus-event-dashboard-history summary {
                cursor: pointer;
                font-weight: 600;
            }

            .barnahus-event-dashboard-archive summary {
                cursor: pointer;
                font-weight: 600;
            }

            .barnahus-event-dashboard-archive__list {
                display: grid;
                gap: 8px;
                margin-top: 12px;
            }

            .barnahus-event-dashboard-archive__item {
                display: flex;
                flex-wrap: wrap;
                gap: 10px 16px;
                align-items: center;
                justify-content: space-between;
                background: #fff;
                border: 1px solid #dcdcde;
                padding: 10px 12px;
            }

            .barnahus-event-dashboard-archive__item p {
                margin: 0;
            }

            .barnahus-event-dashboard-archive__actions {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                align-items: center;
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

            .barnahus-event-dashboard-card__actions {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                align-items: center;
                justify-content: flex-end;
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

            .barnahus-event-dashboard-card__url-row {
                display: flex;
                gap: 8px;
                align-items: stretch;
            }

            .barnahus-event-dashboard-card__url-row input[type="url"] {
                min-width: 0;
            }

            .barnahus-event-dashboard-card__url-row .button {
                display: inline-flex;
                align-items: center;
                min-height: 40px;
                white-space: nowrap;
            }

            .barnahus-event-dashboard-card__post-action {
                margin-top: 10px;
            }

            .barnahus-event-dashboard-shell {
                display: grid;
                grid-template-columns: 300px minmax(0, 1fr);
                gap: 0;
                max-width: 1500px;
                background: #fff;
                border: 1px solid #c3c4c7;
            }

            .barnahus-event-dashboard-tabs {
                display: flex;
                flex-wrap: wrap;
                gap: 0;
                max-width: 1500px;
                margin-top: 16px;
                border: 1px solid #c3c4c7;
                border-bottom: 0;
                background: #fff;
                padding: 0 16px;
            }

            .barnahus-event-dashboard-tab {
                border: 0;
                border-bottom: 3px solid transparent;
                background: transparent;
                color: #646970;
                cursor: pointer;
                font-weight: 600;
                padding: 13px 14px 10px;
            }

            .barnahus-event-dashboard-tab.is-active {
                border-bottom-color: #3858e9;
                color: #1d2327;
            }

            .barnahus-event-dashboard-tab__count {
                color: #646970;
                font-weight: 400;
            }

            .barnahus-event-dashboard-sidebar {
                display: grid;
                align-content: start;
                gap: 12px;
                border-right: 1px solid #dcdcde;
                background: #f6f7f7;
                padding: 16px;
            }

            .barnahus-event-dashboard-search {
                width: 100%;
                min-height: 36px;
            }

            .barnahus-event-dashboard-list {
                display: grid;
                gap: 8px;
                max-height: 780px;
                overflow: auto;
                padding-right: 2px;
            }

            .barnahus-event-dashboard-list__item {
                display: grid;
                gap: 4px;
                width: 100%;
                border: 1px solid transparent;
                background: #fff;
                color: #1d2327;
                cursor: pointer;
                padding: 10px;
                text-align: left;
            }

            .barnahus-event-dashboard-list__item:hover,
            .barnahus-event-dashboard-list__item.is-active {
                border-color: #3858e9;
                box-shadow: inset 3px 0 0 #3858e9;
            }

            .barnahus-event-dashboard-list__title {
                font-weight: 600;
            }

            .barnahus-event-dashboard-list__meta {
                color: #646970;
                font-size: 12px;
            }

            .barnahus-event-dashboard-editor {
                min-width: 0;
                background: #fff;
            }

            .barnahus-event-dashboard {
                max-width: none;
            }

            .barnahus-event-dashboard-card {
                display: none;
                border: 0;
                border-bottom: 1px solid #dcdcde;
                padding: 0;
            }

            .barnahus-event-dashboard-card.is-active {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 380px;
                min-height: 720px;
            }

            .barnahus-event-dashboard-card__main {
                display: grid;
                align-content: start;
                gap: 18px;
                padding: 24px;
            }

            .barnahus-event-dashboard-card__inspector {
                display: grid;
                align-content: start;
                gap: 16px;
                border-left: 1px solid #dcdcde;
                background: #fbfbfc;
                padding: 18px;
            }

            .barnahus-event-dashboard-card__preview {
                max-width: 700px;
            }

            .barnahus-event-dashboard-card__preview .bh-event-card {
                display: grid;
                grid-template-columns: 50px minmax(0, 1fr);
                gap: 14px;
                min-height: 340px;
                box-sizing: border-box;
                background: rgba(255, 255, 255, 0.64);
                border: 1px solid transparent;
                padding: 18px;
            }

            .barnahus-event-dashboard-card__preview .bh-event-card {
                min-height: 340px;
            }

            .barnahus-event-dashboard-card__preview .bh-event-card.is-featured.is-pinned {
                background: rgba(174, 185, 238, 0.24);
                border-color: rgba(174, 185, 238, 0.72);
                border-top: 3px solid #8f9fe8;
            }

            .barnahus-event-dashboard-card__preview .bh-event-date {
                display: block;
                border-right: 1px solid rgba(17, 17, 17, 0.12);
                color: #111;
                font-weight: 800;
                padding-right: 11px;
                text-decoration: none;
            }

            .barnahus-event-dashboard-card__preview .bh-event-day,
            .barnahus-event-dashboard-card__preview .bh-event-month {
                display: block;
            }

            .barnahus-event-dashboard-card__preview .bh-event-day {
                font-size: 29px;
                line-height: 1;
            }

            .barnahus-event-dashboard-card__preview .bh-event-month {
                margin-top: 4px;
                font-size: 12px;
                text-transform: uppercase;
            }

            .barnahus-event-dashboard-card__preview .bh-event-content {
                display: flex;
                flex-direction: column;
                min-width: 0;
            }

            .barnahus-event-dashboard-card__preview .bh-event-tags {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
                margin: 0 0 10px;
            }

            .barnahus-event-dashboard-card__preview .bh-event-tag {
                display: inline-flex;
                min-height: 20px;
                align-items: center;
                border: 1px solid rgba(174, 185, 238, 0.72);
                background: rgba(217, 222, 244, 0.72);
                color: #2d2d2d;
                font-size: 11px;
                font-weight: 700;
                line-height: 1.2;
                padding: 1px 6px;
            }

            .barnahus-event-dashboard-card__preview .bh-event-tag--status {
                border-color: rgba(17, 17, 17, 0.14);
                background: rgba(255, 255, 255, 0.6);
            }

            .barnahus-event-dashboard-card__preview .bh-event-title {
                margin: 0 0 7px;
                font-size: 19px;
                line-height: 1.18;
            }

            .barnahus-event-dashboard-card__preview .bh-event-meta,
            .barnahus-event-dashboard-card__preview .bh-event-description {
                margin: 0;
                color: #3c434a;
                font-family: Georgia, "Times New Roman", serif;
                font-size: 16px;
                line-height: 1.48;
            }

            .barnahus-event-dashboard-card__preview .bh-event-description {
                margin-top: 8px;
                color: #111;
            }

            .barnahus-event-dashboard-card__preview .bh-event-link {
                display: inline-block;
                width: fit-content;
                margin-top: auto;
                border-bottom: 2px solid currentColor;
                color: #111;
                font-weight: 800;
                text-decoration: none;
            }

            .barnahus-event-dashboard-card__plain-link {
                display: inline-flex;
                width: fit-content;
                color: #3858e9;
                font-weight: 500;
                text-decoration: none;
                word-break: break-word;
            }

            .barnahus-event-dashboard-card__plain-link:hover {
                color: #1e3a8a;
                text-decoration: underline;
            }

            .barnahus-event-dashboard-empty {
                border: 0;
                padding: 24px;
            }

            .barnahus-event-dashboard-savebar {
                position: sticky;
                bottom: 0;
                z-index: 2;
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                align-items: center;
                justify-content: flex-end;
                border-top: 1px solid #c3c4c7;
                background: rgba(255, 255, 255, .96);
                padding: 12px 16px;
            }

            @media (max-width: 782px) {
                .barnahus-event-dashboard-card__full {
                    grid-column: auto;
                }

                .barnahus-event-dashboard-shell,
                .barnahus-event-dashboard-card.is-active {
                    grid-template-columns: 1fr;
                }

                .barnahus-event-dashboard-sidebar,
                .barnahus-event-dashboard-card__inspector {
                    border-left: 0;
                    border-right: 0;
                }
            }
        </style>

        <div class="barnahus-event-dashboard-tools">
            <p>
                Luma source: <a href="<?php echo esc_url(BARNAHUS_EVENT_LUMA_CALENDAR_URL); ?>">Network Events</a>
                <?php if ($last_luma_refresh) : ?>
                    <br><span class="description">Last refreshed <?php echo esc_html(barnahus_format_dashboard_datetime((int) $last_luma_refresh)); ?>.</span>
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

        <div class="barnahus-event-dashboard-tip">
            <p><strong>Tip:</strong> press Ctrl+Enter or Cmd+Enter to save event display settings.</p>
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
                                    <?php echo esc_html(barnahus_format_dashboard_datetime(isset($snapshot['created_at']) ? (int) $snapshot['created_at'] : time())); ?>
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

        <details class="barnahus-event-dashboard-create">
            <summary>Add planned event card</summary>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
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
                    <input type="text" id="barnahus_new_event_date" name="new_event[date]" placeholder="YYYY-MM-DD" pattern="\d{4}-\d{2}-\d{2}" inputmode="numeric" title="Use ISO format: YYYY-MM-DD">
                </div>

                <div class="barnahus-event-dashboard-card__field">
                    <label for="barnahus_new_event_location">Location / platform</label>
                    <input type="text" id="barnahus_new_event_location" name="new_event[location]" placeholder="Online, city, or venue">
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
                    <label>
                        <input type="checkbox" name="new_event[hide_date]" value="1">
                        Hide date on card
                    </label>
                </div>

                <div class="barnahus-event-dashboard-card__field barnahus-event-dashboard-card__full">
                    <label for="barnahus_new_event_excerpt">Card intro</label>
                    <textarea id="barnahus_new_event_excerpt" name="new_event[excerpt]" rows="3" placeholder="Short text shown on the event card"></textarea>
                </div>

                <div class="barnahus-event-dashboard-card__field barnahus-event-dashboard-card__full">
                    <div class="barnahus-event-dashboard-card__link-settings">
                        <div class="barnahus-event-dashboard-card__field">
                            <label for="barnahus_new_event_card_link_type">Card link</label>
                            <select id="barnahus_new_event_card_link_type" name="new_event[card_link_type]">
                                <?php foreach (barnahus_get_card_link_type_options() as $link_type_value => $link_type_label) : ?>
                                    <option value="<?php echo esc_attr($link_type_value); ?>" <?php selected('custom', $link_type_value); ?>><?php echo esc_html($link_type_label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="barnahus-event-dashboard-card__field">
                            <label for="barnahus_new_event_registration_url">Luma URL</label>
                            <input type="url" id="barnahus_new_event_registration_url" name="new_event[registration_url]" placeholder="Set by Luma refresh" readonly>
                        </div>

                        <div class="barnahus-event-dashboard-card__field">
                            <label for="barnahus_new_event_custom_url">Manual URL</label>
                            <input type="url" id="barnahus_new_event_custom_url" name="new_event[custom_url]" placeholder="Any website, Zoom, or registration URL">
                        </div>
                    </div>
                    <p class="barnahus-event-dashboard-card__link-note">Luma URL uses the synced event URL. Manual URL uses whatever URL you enter. Automatic post uses a WordPress post only after you create one.</p>
                </div>
            </div>

            <?php submit_button('Create event card', 'secondary', 'submit', false); ?>
        </form>
        </details>

        <form id="barnahus-event-dashboard-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="barnahus_save_events_dashboard">
            <?php wp_nonce_field('barnahus_save_events_dashboard', 'barnahus_events_dashboard_nonce'); ?>

            <div class="barnahus-event-dashboard-tabs" role="tablist" aria-label="Filter events">
                <button class="barnahus-event-dashboard-tab is-active" type="button" data-event-filter="active">Active <span class="barnahus-event-dashboard-tab__count"><?php echo esc_html(count($active_events)); ?></span></button>
                <button class="barnahus-event-dashboard-tab" type="button" data-event-filter="featured">Featured <span class="barnahus-event-dashboard-tab__count"><?php echo esc_html(count(array_filter($active_events, function ($event) { return get_post_meta($event->ID, '_barnahus_event_featured', true) === '1'; }))); ?></span></button>
                <button class="barnahus-event-dashboard-tab" type="button" data-event-filter="pinned">Pinned <span class="barnahus-event-dashboard-tab__count"><?php echo esc_html(count(array_filter($active_events, function ($event) { return barnahus_is_event_pinned($event->ID); }))); ?></span></button>
                <button class="barnahus-event-dashboard-tab" type="button" data-event-filter="tba">TBA <span class="barnahus-event-dashboard-tab__count"><?php echo esc_html(count(array_filter($active_events, function ($event) { return !get_post_meta($event->ID, '_barnahus_event_date', true); }))); ?></span></button>
                <button class="barnahus-event-dashboard-tab" type="button" data-event-filter="archive">Archive <span class="barnahus-event-dashboard-tab__count"><?php echo esc_html(count($archived_events)); ?></span></button>
            </div>

            <div class="barnahus-event-dashboard-shell">
                <aside class="barnahus-event-dashboard-sidebar">
                    <input class="barnahus-event-dashboard-search" type="search" placeholder="Search events" aria-label="Search events" data-event-search>
                    <div class="barnahus-event-dashboard-list" data-event-list>
                        <?php foreach ($dashboard_events as $event) : ?>
                            <?php
                            $list_post_id = $event->ID;
                            $list_date = get_post_meta($list_post_id, '_barnahus_event_date', true);
                            $list_start_time = get_post_meta($list_post_id, '_barnahus_event_start_time', true);
                            $list_end_time = get_post_meta($list_post_id, '_barnahus_event_end_time', true);
                            $list_location = barnahus_get_event_location($list_post_id);
                            $list_featured = get_post_meta($list_post_id, '_barnahus_event_featured', true) === '1';
                            $list_pinned = barnahus_is_event_pinned($list_post_id);
                            $list_archived = barnahus_event_is_archived($list_post_id);
                            $list_tba = !$list_date;
                            $list_filters = array($list_archived ? 'archive' : 'active');

                            if (!$list_archived && $list_featured) {
                                $list_filters[] = 'featured';
                            }

                            if (!$list_archived && $list_pinned) {
                                $list_filters[] = 'pinned';
                            }

                            if (!$list_archived && $list_tba) {
                                $list_filters[] = 'tba';
                            }
                            ?>
                            <button class="barnahus-event-dashboard-list__item" type="button" data-event-list-item data-event-id="<?php echo esc_attr($list_post_id); ?>" data-event-filters="<?php echo esc_attr(implode(' ', $list_filters)); ?>" data-event-search-text="<?php echo esc_attr(strtolower(get_the_title($event) . ' ' . implode(' ', barnahus_get_event_series_names($list_post_id)) . ' ' . barnahus_format_event_dashboard_meta($list_date, $list_start_time, $list_end_time, $list_location))); ?>">
                                <span class="barnahus-event-dashboard-list__title"><?php echo esc_html(get_the_title($event)); ?></span>
                                <span class="barnahus-event-dashboard-list__meta">
                                    <?php echo esc_html(barnahus_format_event_dashboard_meta($list_date, $list_start_time, $list_end_time, $list_location)); ?>
                                    <?php if ($list_featured) : ?> · Featured<?php endif; ?>
                                    <?php if ($list_pinned) : ?> · Pinned<?php endif; ?>
                                    <?php if ($list_archived) : ?> · Archived<?php endif; ?>
                                </span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </aside>

                <div class="barnahus-event-dashboard-editor">
                    <div class="barnahus-event-dashboard">
                        <?php if (!$dashboard_events) : ?>
                            <div class="barnahus-event-dashboard-empty">No Barnahus events found.</div>
                <?php endif; ?>

                <?php foreach ($dashboard_events as $event) : ?>
                    <?php
                    $post_id = $event->ID;
                    $date = get_post_meta($post_id, '_barnahus_event_date', true);
                    $start_time = get_post_meta($post_id, '_barnahus_event_start_time', true);
                    $end_time = get_post_meta($post_id, '_barnahus_event_end_time', true);
                    $location = barnahus_get_event_location($post_id);
                    $registration_url = get_post_meta($post_id, '_barnahus_event_luma_url', true);
                    $custom_url = get_post_meta($post_id, '_barnahus_event_custom_url', true);
                    $card_link_type = barnahus_normalize_card_link_type(get_post_meta($post_id, '_barnahus_event_card_link_type', true));
                    $is_wordpress_post = BARNAHUS_EVENT_CANONICAL_POST_TYPE === get_post_type($post_id);
                    $linked_post_id = $is_wordpress_post ? 0 : barnahus_get_event_linked_post_id($post_id);
                    $featured = get_post_meta($post_id, '_barnahus_event_featured', true) === '1';
                    $pinned = barnahus_is_event_pinned($post_id);
                    $archived = get_post_meta($post_id, '_barnahus_event_archived', true) === '1';
                    $hidden = get_post_meta($post_id, '_barnahus_event_hidden', true) === '1';
                    $hide_date = get_post_meta($post_id, '_barnahus_event_hide_date', true) === '1';
                    $registration_status = get_post_meta($post_id, '_barnahus_event_registration_status', true);
                    $series = barnahus_get_event_series_names($post_id);
                    $excerpt = get_the_excerpt($event);
                    $event_filters = array($archived ? 'archive' : 'active');

                    if (!$archived && $featured) {
                        $event_filters[] = 'featured';
                    }

                    if (!$archived && $pinned) {
                        $event_filters[] = 'pinned';
                    }

                    if (!$archived && !$date) {
                        $event_filters[] = 'tba';
                    }
                    ?>
                    <section class="barnahus-event-dashboard-card" data-event-panel data-event-id="<?php echo esc_attr($post_id); ?>" data-event-filters="<?php echo esc_attr(implode(' ', $event_filters)); ?>">
                        <div class="barnahus-event-dashboard-card__main">
                        <div class="barnahus-event-dashboard-card__header">
                            <div>
                                <h2 class="barnahus-event-dashboard-card__title"><?php echo esc_html(get_the_title($event)); ?></h2>
                                <div class="barnahus-event-dashboard-card__meta">
                                    <?php echo esc_html(barnahus_format_event_dashboard_meta($date, $start_time, $end_time, $location)); ?>
                                    <?php echo esc_html(' · ' . barnahus_get_event_dashboard_state($post_id, $event->post_status)); ?>
                                </div>
                            </div>
                            <div class="barnahus-event-dashboard-card__actions">
                                <a class="button button-secondary" href="<?php echo esc_url(get_edit_post_link($post_id)); ?>"><?php echo $is_wordpress_post ? 'Edit WordPress post' : 'Edit event record'; ?></a>
                                <?php if ($linked_post_id) : ?>
                                    <a class="button button-secondary" href="<?php echo esc_url(get_edit_post_link($linked_post_id)); ?>">Edit linked post</a>
                                    <?php if ('publish' === get_post_status($linked_post_id)) : ?>
                                        <a class="button button-secondary" href="<?php echo esc_url(get_permalink($linked_post_id)); ?>">View post</a>
                                    <?php endif; ?>
                                <?php elseif ($is_wordpress_post && 'publish' === $event->post_status) : ?>
                                    <a class="button button-secondary" href="<?php echo esc_url(get_permalink($event)); ?>">View post</a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="barnahus-event-dashboard-card__preview">
                            <?php echo barnahus_render_event_card($event, array('variant' => 'quiet', 'description_words' => 28)); ?>
                        </div>
                        </div>

                        <div class="barnahus-event-dashboard-card__inspector">
                        <div class="barnahus-event-dashboard-card__fields">
                            <div class="barnahus-event-dashboard-card__field barnahus-event-dashboard-card__full">
                                <label for="barnahus_event_title_<?php echo esc_attr($post_id); ?>">Event title</label>
                                <input type="text" id="barnahus_event_title_<?php echo esc_attr($post_id); ?>" name="events[<?php echo esc_attr($post_id); ?>][title]" value="<?php echo esc_attr(get_the_title($event)); ?>">
                            </div>

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
                                <label>
                                    <input type="checkbox" name="events[<?php echo esc_attr($post_id); ?>][archived]" value="1" <?php checked($archived); ?>>
                                    Archived
                                </label>
                                <label>
                                    <input type="checkbox" name="events[<?php echo esc_attr($post_id); ?>][hide_date]" value="1" <?php checked($hide_date); ?>>
                                    Hide date on card
                                </label>
                            </div>

                            <div class="barnahus-event-dashboard-card__field">
                                <label for="barnahus_event_date_<?php echo esc_attr($post_id); ?>">Date</label>
                                <input type="text" id="barnahus_event_date_<?php echo esc_attr($post_id); ?>" name="events[<?php echo esc_attr($post_id); ?>][date]" value="<?php echo esc_attr($date); ?>" placeholder="YYYY-MM-DD" pattern="\d{4}-\d{2}-\d{2}" inputmode="numeric" title="Use ISO format: YYYY-MM-DD">
                            </div>

                            <div class="barnahus-event-dashboard-card__field">
                                <label for="barnahus_event_location_<?php echo esc_attr($post_id); ?>">Location / platform</label>
                                <input type="text" id="barnahus_event_location_<?php echo esc_attr($post_id); ?>" name="events[<?php echo esc_attr($post_id); ?>][location]" value="<?php echo esc_attr($location); ?>" placeholder="Online, city, or venue">
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
                                        <label for="barnahus_event_card_link_type_<?php echo esc_attr($post_id); ?>">Card link</label>
                                        <select id="barnahus_event_card_link_type_<?php echo esc_attr($post_id); ?>" name="events[<?php echo esc_attr($post_id); ?>][card_link_type]" class="barnahus-event-card-link-type">
                                            <?php foreach (barnahus_get_card_link_type_options() as $link_type_value => $link_type_label) : ?>
                                                <option value="<?php echo esc_attr($link_type_value); ?>" <?php selected($card_link_type, $link_type_value); ?>><?php echo esc_html($link_type_label); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="barnahus-event-dashboard-card__field">
                                        <label for="barnahus_event_registration_url_<?php echo esc_attr($post_id); ?>">Luma URL</label>
                                        <?php if ($registration_url) : ?>
                                            <a class="barnahus-event-dashboard-card__plain-link" id="barnahus_event_registration_url_<?php echo esc_attr($post_id); ?>" href="<?php echo esc_url($registration_url); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($registration_url); ?></a>
                                        <?php else : ?>
                                            <span class="description" id="barnahus_event_registration_url_<?php echo esc_attr($post_id); ?>">Set by Luma refresh</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="barnahus-event-dashboard-card__field">
                                        <label for="barnahus_event_custom_url_<?php echo esc_attr($post_id); ?>">Manual URL</label>
                                        <input type="url" id="barnahus_event_custom_url_<?php echo esc_attr($post_id); ?>" name="events[<?php echo esc_attr($post_id); ?>][custom_url]" value="<?php echo esc_url($custom_url); ?>" placeholder="Any website, Zoom, or registration URL" class="barnahus-event-manual-url" data-card-link-select="barnahus_event_card_link_type_<?php echo esc_attr($post_id); ?>" data-initial-manual-url="<?php echo esc_attr($custom_url); ?>">
                                    </div>
                                </div>
                                <p class="barnahus-event-dashboard-card__link-note">Luma URL uses the synced event URL. Manual URL uses whatever URL you enter. Automatic post uses a WordPress post only after you create one.</p>
                                <?php if (BARNAHUS_EVENT_CANONICAL_POST_TYPE !== get_post_type($post_id)) : ?>
                                    <div class="barnahus-event-dashboard-card__post-action">
                                        <?php if ($linked_post_id) : ?>
                                            <span class="description">Automatic post created.</span>
                                        <?php else : ?>
                                            <a class="button button-secondary" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=barnahus_create_event_post_page&event_id=' . absint($post_id)), 'barnahus_create_event_post_page_' . absint($post_id), 'barnahus_create_event_post_page_nonce')); ?>">Create automatic post</a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        </div>
                    </section>
                <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="barnahus-event-dashboard-savebar">
                <span class="description">Shortcut: Ctrl+Enter or Cmd+Enter</span>
                <?php submit_button('Save event display settings', 'primary', 'submit', false); ?>
            </div>
        </form>
    </div>
    <script>
        (function () {
            var form = document.getElementById('barnahus-event-dashboard-form');

            if (!form) {
                return;
            }

            var currentFilter = 'active';
            var searchInput = document.querySelector('[data-event-search]');
            var tabs = Array.prototype.slice.call(document.querySelectorAll('[data-event-filter]'));
            var listItems = Array.prototype.slice.call(document.querySelectorAll('[data-event-list-item]'));
            var panels = Array.prototype.slice.call(document.querySelectorAll('[data-event-panel]'));

            function itemMatchesFilter(item) {
                var filters = (item.getAttribute('data-event-filters') || '').split(' ');
                var search = searchInput ? searchInput.value.trim().toLowerCase() : '';
                var searchText = item.getAttribute('data-event-search-text') || '';

                return filters.indexOf(currentFilter) !== -1 && (!search || searchText.indexOf(search) !== -1);
            }

            function showEvent(eventId) {
                listItems.forEach(function (item) {
                    item.classList.toggle('is-active', item.getAttribute('data-event-id') === eventId);
                });

                panels.forEach(function (panel) {
                    panel.classList.toggle('is-active', panel.getAttribute('data-event-id') === eventId);
                });
            }

            function refreshVisibleEvents(preferredEventId) {
                var firstVisible = null;

                listItems.forEach(function (item) {
                    var isVisible = itemMatchesFilter(item);
                    item.hidden = !isVisible;

                    if (isVisible && !firstVisible) {
                        firstVisible = item.getAttribute('data-event-id');
                    }
                });

                panels.forEach(function (panel) {
                    panel.hidden = (panel.getAttribute('data-event-filters') || '').split(' ').indexOf(currentFilter) === -1;
                });

                if (preferredEventId) {
                    var preferredItem = listItems.filter(function (item) {
                        return item.getAttribute('data-event-id') === preferredEventId && !item.hidden;
                    })[0];

                    if (preferredItem) {
                        showEvent(preferredEventId);
                        return;
                    }
                }

                if (firstVisible) {
                    showEvent(firstVisible);
                    return;
                }

                panels.forEach(function (panel) {
                    panel.classList.remove('is-active');
                });
            }

            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    currentFilter = tab.getAttribute('data-event-filter') || 'active';
                    tabs.forEach(function (otherTab) {
                        otherTab.classList.toggle('is-active', otherTab === tab);
                    });
                    refreshVisibleEvents();
                });
            });

            listItems.forEach(function (item) {
                item.addEventListener('click', function () {
                    showEvent(item.getAttribute('data-event-id'));
                });
            });

            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    var activeItem = document.querySelector('[data-event-list-item].is-active');
                    refreshVisibleEvents(activeItem ? activeItem.getAttribute('data-event-id') : '');
                });
            }

            refreshVisibleEvents();

            document.addEventListener('keydown', function (event) {
                if (!(event.metaKey || event.ctrlKey) || event.key !== 'Enter') {
                    return;
                }

                event.preventDefault();

                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                    return;
                }

                form.submit();
            });

            Array.prototype.forEach.call(document.querySelectorAll('.barnahus-event-manual-url'), function (input) {
                var select = document.getElementById(input.getAttribute('data-card-link-select'));
                var hadManualUrl = Boolean(input.getAttribute('data-initial-manual-url'));

                if (!select || hadManualUrl) {
                    return;
                }

                input.addEventListener('input', function () {
                    if (input.value.trim()) {
                        select.value = 'custom';
                    }
                });
            });
        }());
    </script>
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
        update_post_meta($post_id, '_barnahus_event_archived', isset($event_fields['archived']) ? '1' : '0');
        update_post_meta($post_id, '_barnahus_event_hidden', isset($event_fields['hidden']) ? '1' : '0');
        update_post_meta($post_id, '_barnahus_event_hide_date', isset($event_fields['hide_date']) ? '1' : '0');
        update_post_meta($post_id, '_barnahus_event_date', isset($event_fields['date']) ? barnahus_normalize_event_date($event_fields['date']) : '');
        update_post_meta($post_id, '_barnahus_event_location', isset($event_fields['location']) ? barnahus_normalize_event_location($event_fields['location']) : '');
        $card_link_type = barnahus_resolve_submitted_event_card_link_type($event_fields, $post_id, 'registration');
        update_post_meta($post_id, '_barnahus_event_custom_url', isset($event_fields['custom_url']) ? esc_url_raw($event_fields['custom_url']) : '');
        update_post_meta($post_id, '_barnahus_event_card_link_type', $card_link_type);
        update_post_meta($post_id, '_barnahus_event_registration_status', isset($event_fields['registration_status']) ? barnahus_normalize_registration_status($event_fields['registration_status']) : '');

        $series_names = isset($event_fields['series']) ? barnahus_parse_event_series_names($event_fields['series']) : array();
        barnahus_set_event_series_names($post_id, $series_names);

        $post_update = array('ID' => $post_id);

        if (isset($event_fields['title'])) {
            $post_update['post_title'] = sanitize_text_field($event_fields['title']);
        }

        if (isset($event_fields['excerpt'])) {
            $post_update['post_excerpt'] = sanitize_textarea_field($event_fields['excerpt']);
        }

        if (count($post_update) > 1) {
            wp_update_post($post_update);
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
            'post_type' => BARNAHUS_EVENT_POST_TYPE,
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

    update_post_meta($post_id, '_barnahus_event_date', isset($event_fields['date']) ? barnahus_normalize_event_date($event_fields['date']) : '');
    update_post_meta($post_id, '_barnahus_event_start_time', '');
    update_post_meta($post_id, '_barnahus_event_end_time', '');
    update_post_meta($post_id, '_barnahus_event_location', isset($event_fields['location']) ? barnahus_normalize_event_location($event_fields['location']) : '');
    update_post_meta($post_id, '_barnahus_event_luma_url', isset($event_fields['registration_url']) ? esc_url_raw($event_fields['registration_url']) : '');
    update_post_meta($post_id, '_barnahus_event_luma_embed_url', '');
    $card_link_type = barnahus_resolve_submitted_event_card_link_type($event_fields, $post_id, 'custom');
    update_post_meta($post_id, '_barnahus_event_custom_url', isset($event_fields['custom_url']) ? esc_url_raw($event_fields['custom_url']) : '');
    update_post_meta($post_id, '_barnahus_event_card_link_type', $card_link_type);
    update_post_meta($post_id, '_barnahus_event_registration_status', isset($event_fields['registration_status']) ? barnahus_normalize_registration_status($event_fields['registration_status']) : 'coming-soon');
    update_post_meta($post_id, '_barnahus_event_featured', isset($event_fields['featured']) ? '1' : '0');
    update_post_meta($post_id, '_barnahus_event_pinned', isset($event_fields['pinned']) ? '1' : '0');
    update_post_meta($post_id, '_barnahus_event_archived', '0');
    update_post_meta($post_id, '_barnahus_event_hide_date', isset($event_fields['hide_date']) ? '1' : '0');
    update_post_meta($post_id, '_barnahus_event_hidden', '0');

    $series_names = isset($event_fields['series']) ? barnahus_parse_event_series_names($event_fields['series']) : array();
    barnahus_set_event_series_names($post_id, $series_names);

    wp_safe_redirect(barnahus_get_events_dashboard_url(array('barnahus_event_created' => '1')));
    exit;
}

function barnahus_create_event_post_page_from_dashboard() {
    $event_id = isset($_REQUEST['event_id']) ? absint($_REQUEST['event_id']) : 0;

    if (!$event_id || !current_user_can('edit_post', $event_id)) {
        wp_die('You do not have permission to create an event post.');
    }

    if (!isset($_REQUEST['barnahus_create_event_post_page_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['barnahus_create_event_post_page_nonce'])), 'barnahus_create_event_post_page_' . $event_id)) {
        wp_die('The event post form could not be verified.');
    }

    if (!barnahus_is_event_post($event_id)) {
        wp_die('That event could not be found.');
    }

    $linked_post_id = barnahus_get_event_linked_post_id($event_id);

    if (!$linked_post_id) {
        barnahus_capture_event_dashboard_snapshot('Before automatic post creation');
        $linked_post_id = barnahus_create_event_post_page($event_id);
    }

    if (is_wp_error($linked_post_id)) {
        wp_die(esc_html($linked_post_id->get_error_message()));
    }

    wp_safe_redirect(barnahus_get_events_dashboard_url(array('barnahus_event_post_created' => '1', 'post_id' => absint($linked_post_id))));
    exit;
}

function barnahus_unarchive_event_from_dashboard() {
    $event_id = isset($_REQUEST['event_id']) ? absint($_REQUEST['event_id']) : 0;

    if (!$event_id || !current_user_can('edit_post', $event_id)) {
        wp_die('You do not have permission to unarchive this event.');
    }

    if (!isset($_REQUEST['barnahus_unarchive_event_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['barnahus_unarchive_event_nonce'])), 'barnahus_unarchive_event_' . $event_id)) {
        wp_die('The event archive form could not be verified.');
    }

    if (!barnahus_is_event_post($event_id)) {
        wp_die('That event could not be found.');
    }

    barnahus_capture_event_dashboard_snapshot('Before event unarchive');
    update_post_meta($event_id, '_barnahus_event_archived', '0');

    wp_safe_redirect(barnahus_get_events_dashboard_url(array('barnahus_event_unarchived' => '1')));
    exit;
}

function barnahus_create_event_post_page($event_id) {
    $event = get_post($event_id);

    if (!$event) {
        return new WP_Error('barnahus_event_missing', 'The event could not be found.');
    }

    if (BARNAHUS_EVENT_CANONICAL_POST_TYPE === get_post_type($event_id)) {
        update_post_meta($event_id, '_barnahus_event_card_link_type', 'automatic-post');
        return $event_id;
    }

    $post_id = wp_insert_post(
        array(
            'post_type' => BARNAHUS_EVENT_CANONICAL_POST_TYPE,
            'post_status' => 'draft',
            'post_title' => $event->post_title,
            'post_excerpt' => $event->post_excerpt,
            'post_content' => '',
        ),
        true
    );

    if (is_wp_error($post_id)) {
        return $post_id;
    }

    foreach (barnahus_get_event_snapshot_meta($event_id) as $meta_key => $meta_value) {
        update_post_meta($post_id, $meta_key, $meta_value);
    }

    update_post_meta($event_id, '_barnahus_event_linked_post_id', $post_id);
    update_post_meta($event_id, '_barnahus_event_card_link_type', 'automatic-post');
    update_post_meta($post_id, '_barnahus_event_source_record_id', $event_id);
    update_post_meta($post_id, '_barnahus_event_card_link_type', 'automatic-post');

    barnahus_set_event_series_names($post_id, barnahus_get_event_series_names($event_id));

    return $post_id;
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
        return barnahus_normalize_event_location($location['name']);
    }

    if (is_string($location) && $location) {
        return barnahus_normalize_event_location($location);
    }

    if (false !== strpos($attendance_mode, 'OnlineEventAttendanceMode')) {
        return 'Online';
    }

    return '';
}

function barnahus_normalize_event_location($location) {
    $location = sanitize_text_field($location);
    $normalized_location = strtolower(trim($location));

    if (in_array($normalized_location, array('to be announced', 'tba', 'to be determined', 'tbd'), true)) {
        return '';
    }

    return $location;
}

function barnahus_get_event_location($post_id) {
    return barnahus_normalize_event_location(get_post_meta($post_id, '_barnahus_event_location', true));
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
            'post_type' => BARNAHUS_EVENT_POST_TYPE,
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
        update_post_meta($post_id, '_barnahus_event_card_link_type', 'registration');
        update_post_meta($post_id, '_barnahus_event_registration_status', 'open');
        update_post_meta($post_id, '_barnahus_event_featured', '0');
        update_post_meta($post_id, '_barnahus_event_pinned', '0');
        update_post_meta($post_id, '_barnahus_event_archived', '0');
        update_post_meta($post_id, '_barnahus_event_hide_date', '0');
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

    if (barnahus_event_is_archived($post_id)) {
        return 'Archived';
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
        'approval-required' => 'Approval required',
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

    if ('approval' === $status || 'requires-approval' === $status) {
        return 'approval-required';
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
        'registration' => 'Luma URL',
        'custom' => 'Manual URL',
        'automatic-post' => 'Automatic post',
    );
}

function barnahus_normalize_card_link_type($link_type) {
    $link_type = sanitize_key($link_type);

    if ('event-page' === $link_type) {
        return 'automatic-post';
    }

    if ('none' === $link_type) {
        return 'custom';
    }

    $allowed_link_types = array_keys(barnahus_get_card_link_type_options());

    if (!$link_type) {
        return 'registration';
    }

    if (!in_array($link_type, $allowed_link_types, true)) {
        return 'registration';
    }

    return $link_type;
}

function barnahus_resolve_submitted_event_card_link_type($event_fields, $post_id = 0, $default = 'registration') {
    $link_type = isset($event_fields['card_link_type']) ? barnahus_normalize_card_link_type($event_fields['card_link_type']) : barnahus_normalize_card_link_type($default);
    $manual_url = isset($event_fields['custom_url']) ? esc_url_raw($event_fields['custom_url']) : '';
    $existing_manual_url = $post_id ? get_post_meta($post_id, '_barnahus_event_custom_url', true) : '';

    if ($manual_url && !$existing_manual_url) {
        return 'custom';
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
    $location = barnahus_get_event_location($post->ID);
    $luma_url = get_post_meta($post->ID, '_barnahus_event_luma_url', true);
    $luma_embed_url = get_post_meta($post->ID, '_barnahus_event_luma_embed_url', true);
    $custom_url = get_post_meta($post->ID, '_barnahus_event_custom_url', true);
    $card_link_type = barnahus_normalize_card_link_type(get_post_meta($post->ID, '_barnahus_event_card_link_type', true));
    $button_label = get_post_meta($post->ID, '_barnahus_event_button_label', true);
    $registration_status = get_post_meta($post->ID, '_barnahus_event_registration_status', true);
    $featured = get_post_meta($post->ID, '_barnahus_event_featured', true);
    $pinned = barnahus_is_event_pinned($post->ID);
    $archived = get_post_meta($post->ID, '_barnahus_event_archived', true);
    $hidden = get_post_meta($post->ID, '_barnahus_event_hidden', true);
    $hide_date = get_post_meta($post->ID, '_barnahus_event_hide_date', true);
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
                <input type="text" id="barnahus_event_date" name="barnahus_event_date" value="<?php echo esc_attr($event_date); ?>" placeholder="YYYY-MM-DD" pattern="\d{4}-\d{2}-\d{2}" inputmode="numeric" title="Use ISO format: YYYY-MM-DD">
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
            <label for="barnahus_event_card_link_type">Card link</label>
            <select id="barnahus_event_card_link_type" name="barnahus_event_card_link_type">
                <?php foreach (barnahus_get_card_link_type_options() as $link_type_value => $link_type_label) : ?>
                    <option value="<?php echo esc_attr($link_type_value); ?>" <?php selected($card_link_type, $link_type_value); ?>><?php echo esc_html($link_type_label); ?></option>
                <?php endforeach; ?>
            </select>
            <p class="description">Luma URL uses the synced event URL. Manual URL uses whatever URL you enter. Automatic post uses a WordPress post only after you create one.</p>
        </div>

        <div class="barnahus-event-field">
            <label for="barnahus_event_luma_url">Luma URL</label>
            <input type="url" id="barnahus_event_luma_url" name="barnahus_event_luma_url" value="<?php echo esc_url($luma_url); ?>">
        </div>

        <div class="barnahus-event-field">
            <label for="barnahus_event_luma_embed_url">Embedded registration URL override</label>
            <input type="url" id="barnahus_event_luma_embed_url" name="barnahus_event_luma_embed_url" value="<?php echo esc_url($luma_embed_url); ?>">
            <p class="description">Usually leave blank. Event pages use the Luma URL automatically when it can be embedded; add an override only if a registration service gives you a separate iframe URL.</p>
        </div>

        <div class="barnahus-event-field">
            <label for="barnahus_event_custom_url">Manual URL</label>
            <input type="url" id="barnahus_event_custom_url" name="barnahus_event_custom_url" value="<?php echo esc_url($custom_url); ?>">
            <p class="description">Optional. Used only when Card link is Manual URL.</p>
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
                <input type="checkbox" name="barnahus_event_archived" value="1" <?php checked($archived, '1'); ?>>
                Archived: keep this event out of the public grid
            </label>
        </div>

        <div class="barnahus-event-field">
            <label>
                <input type="checkbox" name="barnahus_event_hidden" value="1" <?php checked($hidden, '1'); ?>>
                Hidden: do not show this event in the public grid
            </label>
        </div>

        <div class="barnahus-event-field">
            <label>
                <input type="checkbox" name="barnahus_event_hide_date" value="1" <?php checked($hide_date, '1'); ?>>
                Hide date on card
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

    $submitted_card_link_type = barnahus_resolve_submitted_event_card_link_type(
        array(
            'card_link_type' => isset($_POST['barnahus_event_card_link_type']) ? wp_unslash($_POST['barnahus_event_card_link_type']) : '',
            'custom_url' => isset($_POST['barnahus_event_custom_url']) ? wp_unslash($_POST['barnahus_event_custom_url']) : '',
        ),
        $post_id,
        'registration'
    );

    $fields = array(
        '_barnahus_event_date' => array('barnahus_event_date', 'barnahus_normalize_event_date'),
        '_barnahus_event_start_time' => array('barnahus_event_start_time', 'sanitize_text_field'),
        '_barnahus_event_end_time' => array('barnahus_event_end_time', 'sanitize_text_field'),
        '_barnahus_event_location' => array('barnahus_event_location', 'barnahus_normalize_event_location'),
        '_barnahus_event_luma_url' => array('barnahus_event_luma_url', 'esc_url_raw'),
        '_barnahus_event_luma_embed_url' => array('barnahus_event_luma_embed_url', 'esc_url_raw'),
        '_barnahus_event_custom_url' => array('barnahus_event_custom_url', 'esc_url_raw'),
        '_barnahus_event_button_label' => array('barnahus_event_button_label', 'sanitize_text_field'),
        '_barnahus_event_registration_status' => array('barnahus_event_registration_status', 'barnahus_normalize_registration_status'),
    );

    foreach ($fields as $meta_key => $field_config) {
        list($field_name, $sanitize_callback) = $field_config;
        $value = isset($_POST[$field_name]) ? call_user_func($sanitize_callback, wp_unslash($_POST[$field_name])) : '';
        update_post_meta($post_id, $meta_key, $value);
    }

    update_post_meta($post_id, '_barnahus_event_card_link_type', $submitted_card_link_type);
    update_post_meta($post_id, '_barnahus_event_featured', isset($_POST['barnahus_event_featured']) ? '1' : '0');
    update_post_meta($post_id, '_barnahus_event_pinned', isset($_POST['barnahus_event_pinned']) ? '1' : '0');
    update_post_meta($post_id, '_barnahus_event_archived', isset($_POST['barnahus_event_archived']) ? '1' : '0');
    update_post_meta($post_id, '_barnahus_event_hidden', isset($_POST['barnahus_event_hidden']) ? '1' : '0');
    update_post_meta($post_id, '_barnahus_event_hide_date', isset($_POST['barnahus_event_hide_date']) ? '1' : '0');
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
    <?php echo barnahus_render_events_calendar($events, array('columns' => $columns, 'compact' => $compact, 'variant' => $variant, 'min_width' => $min_width, 'description_words' => $description_words, 'show_description' => $show_description, 'featured_order' => $featured_order)); ?>
    <?php
    return ob_get_clean();
}

function barnahus_render_events_calendar($events, $args = array()) {
    $args = wp_parse_args(
        $args,
        array(
            'columns' => 'auto',
            'compact' => false,
            'variant' => 'quiet',
            'min_width' => 360,
            'description_words' => 22,
            'show_description' => true,
            'featured_order' => 'pinned',
        )
    );

    $pinned_events = array();
    $calendar_events = array();

    foreach ($events as $event) {
        $is_featured = get_post_meta($event->ID, '_barnahus_event_featured', true) === '1';
        $is_pinned = barnahus_is_event_pinned($event->ID);

        if ('pinned' === $args['featured_order'] && $is_featured && $is_pinned) {
            $pinned_events[] = $event;
            continue;
        }

        $calendar_events[] = $event;
    }

    $event_args = array(
        'compact' => $args['compact'],
        'variant' => $args['variant'],
        'description_words' => $args['description_words'],
        'show_description' => $args['show_description'],
    );

    ob_start();
    ?>
    <div class="bh-events-calendar">
        <?php if ($pinned_events && $calendar_events) : ?>
            <?php $first_month_key = barnahus_get_event_month_key($calendar_events[0]->ID); ?>
            <?php $first_month_events = array(); ?>
            <?php $later_events = array(); ?>
            <?php foreach ($calendar_events as $event) : ?>
                <?php if (barnahus_get_event_month_key($event->ID) === $first_month_key) : ?>
                    <?php $first_month_events[] = $event; ?>
                <?php else : ?>
                    <?php $later_events[] = $event; ?>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php $top_month_events = array_slice($first_month_events, 0, 3); ?>
            <?php $remaining_events = array_merge(array_slice($first_month_events, 3), $later_events); ?>

            <div class="bh-events-calendar__pinned-layout">
                <div class="bh-events-calendar__pinned-column">
                    <?php foreach ($pinned_events as $event) : ?>
                        <?php echo barnahus_render_event_card($event, $event_args); ?>
                    <?php endforeach; ?>
                </div>

                <?php echo barnahus_render_event_month_section($first_month_key, $top_month_events, $event_args, $args, true); ?>
            </div>

            <?php echo barnahus_render_event_continuation_sections($remaining_events, $event_args, $args, $first_month_key); ?>
        <?php else : ?>
            <?php echo barnahus_render_event_month_sections(array_merge($pinned_events, $calendar_events), $event_args, $args); ?>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

function barnahus_render_event_continuation_sections($events, $event_args, $grid_args, $continued_month_key = '') {
    if (!$events) {
        return '';
    }

    $continued_events = array();
    $remaining_events = array();
    $still_continuing = true;

    foreach ($events as $event) {
        $month_key = barnahus_get_event_month_key($event->ID);

        if ($still_continuing && $month_key === $continued_month_key) {
            $continued_events[] = $event;
            continue;
        }

        $still_continuing = false;
        $remaining_events[] = $event;
    }

    ob_start();
    ?>
    <?php if ($continued_events) : ?>
        <div class="<?php echo esc_attr(barnahus_get_events_grid_classes($grid_args['columns'], $grid_args['compact'], $grid_args['variant'])); ?>" style="<?php echo esc_attr(barnahus_get_events_grid_style($grid_args['columns'], $grid_args['min_width'])); ?>">
            <?php foreach ($continued_events as $event) : ?>
                <?php echo barnahus_render_event_card($event, $event_args); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php echo barnahus_render_event_month_sections($remaining_events, $event_args, $grid_args); ?>
    <?php
    return ob_get_clean();
}

function barnahus_render_event_month_sections($events, $event_args, $grid_args) {
    if (!$events) {
        return '';
    }

    $groups = array();

    foreach ($events as $event) {
        $month_key = barnahus_get_event_month_key($event->ID);

        if (!isset($groups[$month_key])) {
            $groups[$month_key] = array();
        }

        $groups[$month_key][] = $event;
    }

    ob_start();

    foreach ($groups as $month_key => $month_events) {
        echo barnahus_render_event_month_section($month_key, $month_events, $event_args, $grid_args);
    }

    return ob_get_clean();
}

function barnahus_render_event_month_section($month_key, $events, $event_args, $grid_args, $beside_pinned = false) {
    if (!$events) {
        return '';
    }

    $classes = array('bh-event-month-section');

    if ($beside_pinned) {
        $classes[] = 'bh-event-month-section--beside-pinned';
    }

    ob_start();
    ?>
    <section class="<?php echo esc_attr(implode(' ', $classes)); ?>" aria-labelledby="bh-event-month-<?php echo esc_attr(sanitize_html_class($month_key)); ?>">
        <div class="bh-event-month-heading">
            <h2 id="bh-event-month-<?php echo esc_attr(sanitize_html_class($month_key)); ?>"><?php echo esc_html(barnahus_format_event_month_heading($month_key)); ?></h2>
        </div>

        <div class="<?php echo esc_attr(barnahus_get_events_grid_classes($grid_args['columns'], $grid_args['compact'], $grid_args['variant'])); ?>" style="<?php echo esc_attr(barnahus_get_events_grid_style($grid_args['columns'], $grid_args['min_width'])); ?>">
            <?php foreach ($events as $event) : ?>
                <?php echo barnahus_render_event_card($event, $event_args); ?>
            <?php endforeach; ?>
        </div>
    </section>
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

    if (get_post_meta($event->ID, '_barnahus_event_hidden', true) === '1' || barnahus_event_is_archived($event->ID)) {
        return '';
    }

    barnahus_enqueue_events_assets();

    return barnahus_render_event_card($event, array('compact' => $compact, 'variant' => $variant, 'description_words' => $description_words, 'show_description' => $show_description));
}

function barnahus_get_events_for_display($event_time = 'upcoming', $featured_mode = 'first', $series = '', $featured_order = 'pinned') {
    $meta_query = array(
        'relation' => 'AND',
        array(
            'key' => '_barnahus_event_date',
            'compare' => 'EXISTS',
        ),
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
                'value' => wp_date('Y-m-d', time() - (3 * DAY_IN_SECONDS)),
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

            if (barnahus_event_is_archived($event->ID)) {
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

function barnahus_normalize_event_date($date) {
    $date = sanitize_text_field($date);

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return '';
    }

    $parts = explode('-', $date);

    if (!checkdate((int) $parts[1], (int) $parts[2], (int) $parts[0])) {
        return '';
    }

    return $date;
}

function barnahus_get_event_month_key($post_id) {
    $date = get_post_meta($post_id, '_barnahus_event_date', true);

    if (!$date) {
        return 'forthcoming';
    }

    $timestamp = strtotime($date);

    if (!$timestamp) {
        return 'forthcoming';
    }

    return date('Y-m', $timestamp);
}

function barnahus_format_event_month_heading($month_key) {
    if ('forthcoming' === $month_key) {
        return 'TBA';
    }

    $timestamp = strtotime($month_key . '-01');

    if (!$timestamp) {
        return 'TBA';
    }

    return strtoupper(date_i18n('M Y', $timestamp));
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

function barnahus_event_is_archived($post_id) {
    if (get_post_meta($post_id, '_barnahus_event_archived', true) === '1') {
        return true;
    }

    return barnahus_event_is_automatically_archived($post_id);
}

function barnahus_event_is_automatically_archived($post_id) {
    $archive_timestamp = barnahus_get_event_archive_timestamp($post_id);

    return $archive_timestamp && time() >= $archive_timestamp;
}

function barnahus_get_event_archive_timestamp($post_id) {
    $date = get_post_meta($post_id, '_barnahus_event_date', true);

    if (!$date) {
        return 0;
    }

    $end_time = get_post_meta($post_id, '_barnahus_event_end_time', true);
    $time = $end_time ? $end_time : '23:59:59';

    try {
        $event_end = new DateTimeImmutable($date . ' ' . $time, wp_timezone());
    } catch (Exception $exception) {
        return 0;
    }

    return $event_end->modify('+3 days')->getTimestamp();
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
    $location = barnahus_get_event_location($event->ID);
    $featured = get_post_meta($event->ID, '_barnahus_event_featured', true) === '1';
    $pinned = barnahus_is_event_pinned($event->ID);
    $hide_date = get_post_meta($event->ID, '_barnahus_event_hide_date', true) === '1';
    $registration_status_label = barnahus_get_registration_status_label(get_post_meta($event->ID, '_barnahus_event_registration_status', true));
    $series_names = barnahus_get_event_series_names($event->ID);
    $visible_series_names = array_slice($series_names, 0, $featured ? 2 : 1);
    $timestamp = $date ? strtotime($date . ' ' . ($start_time ? $start_time : '00:00')) : false;
    $description = has_excerpt($event->ID)
        ? get_the_excerpt($event)
        : wp_trim_words(wp_strip_all_tags($event->post_content), $args['description_words']);

    $description_words = $featured ? max($args['description_words'], 95) : $args['description_words'];
    $description = wp_trim_words(wp_strip_all_tags($description), $description_words);

    ob_start();
    ?>
    <article class="<?php echo esc_attr(barnahus_get_event_card_classes($featured, $pinned, $args['compact'], $args['variant'], $hide_date)); ?>" aria-labelledby="bh-event-title-<?php echo esc_attr($event->ID); ?>">
        <?php if (!$hide_date) : ?>
            <time class="bh-event-date" datetime="<?php echo esc_attr($date); ?>">
                <span class="bh-event-day"><?php echo esc_html($timestamp ? date_i18n('j', $timestamp) : 'TBA'); ?></span>
                <span class="bh-event-month"><?php echo esc_html($timestamp ? date_i18n('M', $timestamp) : ''); ?></span>
            </time>
        <?php endif; ?>

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
                <?php echo esc_html(barnahus_format_event_meta($hide_date ? '' : $date, '', '', $location)); ?>
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

    if ('registration' === $link_type && $registration_url) {
        return array(
            'url' => $registration_url,
            'label' => 'Read more',
        );
    }

    if ('custom' === $link_type && $custom_url) {
        return array(
            'url' => $custom_url,
            'label' => 'Read more',
        );
    }

    if ('automatic-post' === $link_type) {
        $linked_post_id = barnahus_get_event_linked_post_id($event->ID);

        if ($linked_post_id && 'publish' === get_post_status($linked_post_id)) {
            return array(
                'url' => get_permalink($linked_post_id),
                'label' => 'Read more',
            );
        }

        if (BARNAHUS_EVENT_CANONICAL_POST_TYPE === get_post_type($event->ID) && 'publish' === get_post_status($event->ID)) {
            return array(
                'url' => get_permalink($event),
                'label' => 'Read more',
            );
        }
    }

    return null;
}

function barnahus_get_event_linked_post_id($event_id) {
    if (BARNAHUS_EVENT_CANONICAL_POST_TYPE === get_post_type($event_id)) {
        return absint($event_id);
    }

    $linked_post_id = absint(get_post_meta($event_id, '_barnahus_event_linked_post_id', true));

    if (!$linked_post_id || BARNAHUS_EVENT_CANONICAL_POST_TYPE !== get_post_type($linked_post_id)) {
        return 0;
    }

    return $linked_post_id;
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
    $location = barnahus_get_event_location($post_id);
    $luma_url = get_post_meta($post_id, '_barnahus_event_luma_url', true);
    $registration_embed_url = barnahus_get_event_registration_embed_url($post_id);
    $meta = barnahus_format_event_meta($date, '', '', $location);

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
    $location = barnahus_normalize_event_location($location);
    $timestamp = $date ? strtotime($date . ' ' . ($start_time ? $start_time : '00:00')) : false;

    if ($timestamp) {
        $parts[] = date_i18n('j F Y', $timestamp);
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

function barnahus_format_event_dashboard_meta($date, $start_time, $end_time, $location) {
    $parts = array();
    $location = barnahus_normalize_event_location($location);

    if ($date) {
        $timestamp = strtotime($date);

        if ($timestamp) {
            $parts[] = date('Y-m-d', $timestamp);
        }
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

function barnahus_format_dashboard_datetime($timestamp) {
    return wp_date('c', (int) $timestamp);
}

function barnahus_enqueue_events_assets() {
    wp_enqueue_style(
        'barnahus-events',
        plugin_dir_url(dirname(__FILE__)) . 'css/events.css',
        array(),
        '1.1.0'
    );

    wp_enqueue_script(
        'barnahus-events',
        plugin_dir_url(dirname(__FILE__)) . 'js/events.js',
        array(),
        '1.1.0',
        true
    );
}

function barnahus_normalize_event_columns($columns) {
    if ('auto' === $columns) {
        return 'auto';
    }

    $columns = absint($columns);

    if ($columns < 1 || $columns > 3) {
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

function barnahus_get_event_card_classes($featured, $pinned, $compact, $variant = 'standard', $hide_date = false) {
    $classes = array('bh-event-card');

    if ($featured) {
        $classes[] = 'is-featured';
    }

    if ($pinned) {
        $classes[] = 'is-pinned';
    }

    if ($hide_date) {
        $classes[] = 'has-no-date';
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
            barnahus_get_event_location($post_id)
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
