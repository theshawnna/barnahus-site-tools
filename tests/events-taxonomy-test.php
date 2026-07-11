<?php

define('ABSPATH', __DIR__ . '/');

$GLOBALS['test_post_types'] = array(42 => 'post');
$GLOBALS['test_categories'] = array(42 => array(1, 3));
$GLOBALS['test_tags'] = array(42 => array('event', 'Editorial', 'Webinars'));
$GLOBALS['test_meta'] = array(
    42 => array(
        '_barnahus_event_managed_series' => array('Webinars'),
    ),
);
$GLOBALS['test_terms'] = array(
    'category' => array(
        'event' => (object) array('term_id' => 7, 'name' => 'Event', 'slug' => 'event'),
        'webinars' => (object) array('term_id' => 9, 'name' => 'Webinars', 'slug' => 'webinars'),
    ),
);

function add_action() {}
function add_filter() {}
function add_shortcode() {}
function absint($value) { return abs((int) $value); }
function sanitize_text_field($value) { return trim((string) $value); }
function is_wp_error() { return false; }
function get_post_type($post_id) { return $GLOBALS['test_post_types'][$post_id] ?? ''; }
function get_option($key, $default = false) { return 'default_category' === $key ? 1 : $default; }
function get_post_meta($post_id, $key) { return $GLOBALS['test_meta'][$post_id][$key] ?? ''; }
function update_post_meta($post_id, $key, $value) { $GLOBALS['test_meta'][$post_id][$key] = $value; }
function wp_get_post_categories($post_id) { return $GLOBALS['test_categories'][$post_id] ?? array(); }
function wp_set_post_categories($post_id, $categories) { $GLOBALS['test_categories'][$post_id] = array_values($categories); }
function get_term_by($field, $value, $taxonomy) {
    foreach ($GLOBALS['test_terms'][$taxonomy] ?? array() as $term) {
        if ((string) $term->{$field} === (string) $value) {
            return $term;
        }
    }
    return false;
}
function wp_insert_term($name, $taxonomy, $args = array()) {
    $term_id = 100 + count($GLOBALS['test_terms'][$taxonomy] ?? array());
    $slug = $args['slug'] ?? strtolower($name);
    $GLOBALS['test_terms'][$taxonomy][$slug] = (object) compact('term_id', 'name', 'slug');
    return array('term_id' => $term_id);
}
function wp_remove_object_terms($post_id, $terms, $taxonomy) {
    if ('post_tag' !== $taxonomy) {
        return;
    }
    $GLOBALS['test_tags'][$post_id] = array_values(array_diff($GLOBALS['test_tags'][$post_id] ?? array(), (array) $terms));
}
function wp_set_object_terms($post_id, $terms, $taxonomy, $append = false) {
    if ('post_tag' !== $taxonomy) {
        return;
    }
    $existing = $append ? ($GLOBALS['test_tags'][$post_id] ?? array()) : array();
    $GLOBALS['test_tags'][$post_id] = array_values(array_unique(array_merge($existing, (array) $terms)));
}
function wp_get_post_tags($post_id, $args = array()) { return $GLOBALS['test_tags'][$post_id] ?? array(); }
function is_admin() { return false; }
function is_singular($post_type) { return 'post' === $post_type; }
function in_the_loop() { return true; }
function is_main_query() { return true; }
function get_the_ID() { return 42; }
function get_post_field($field, $post_id = 0) { return 'post_name' === $field ? 'events' : ''; }
function get_the_title($post_id = 0) { return 'Calendar of events and trainings'; }
function esc_html($value) { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }

require dirname(__DIR__) . '/barnahus-site-tools/includes/events.php';

function assert_same($expected, $actual, $message) {
    if ($expected === $actual) {
        return;
    }

    fwrite(STDERR, $message . PHP_EOL);
    fwrite(STDERR, 'Expected: ' . var_export($expected, true) . PHP_EOL);
    fwrite(STDERR, 'Actual:   ' . var_export($actual, true) . PHP_EOL);
    exit(1);
}

barnahus_ensure_event_post_category(42);
sort($GLOBALS['test_categories'][42]);
assert_same(array(3, 7), $GLOBALS['test_categories'][42], 'The Event category should replace only the default category.');

barnahus_assign_event_category(42, 'Webinars');
sort($GLOBALS['test_categories'][42]);
assert_same(array(3, 7, 9), $GLOBALS['test_categories'][42], 'Assigning an event category should preserve editorial categories.');

barnahus_set_event_series_names(42, array('Training'));
sort($GLOBALS['test_tags'][42]);
assert_same(array('Editorial', 'Training', 'event'), $GLOBALS['test_tags'][42], 'Changing event series should preserve unrelated tags.');
assert_same(array('Training'), $GLOBALS['test_meta'][42]['_barnahus_event_managed_series'], 'Managed series metadata should track only event series.');

barnahus_set_event_series_names(42, array());
sort($GLOBALS['test_tags'][42]);
assert_same(array('Editorial', 'event'), $GLOBALS['test_tags'][42], 'Clearing event series should remove only previously managed series tags.');

$snapshot = barnahus_get_event_snapshot_taxonomy(42);
sort($snapshot['categories']);
sort($snapshot['tags']);
assert_same(array(3, 7, 9), $snapshot['categories'], 'Snapshots should include all event post categories.');
assert_same(array('Editorial', 'event'), $snapshot['tags'], 'Snapshots should include all event post tags.');

$calendar_content = barnahus_add_events_page_accessible_heading('<h2>See our publicly scheduled events and trainings below.</h2>');
assert_same(
    '<h1 class="screen-reader-text">Calendar of events and trainings</h1><h2>See our publicly scheduled events and trainings below.</h2>',
    $calendar_content,
    'The events page should receive one accessible h1.'
);
assert_same(
    '<h1>Existing heading</h1>',
    barnahus_add_events_page_accessible_heading('<h1>Existing heading</h1>'),
    'An existing h1 should not be duplicated.'
);

echo "Event taxonomy regression checks passed.\n";
