<?php

define('ABSPATH', __DIR__ . '/');

$is_development_server = 'cli-server' === PHP_SAPI;
$route = $is_development_server ? ($_SERVER['REQUEST_URI'] ?? '/forum/programme') : ($argv[1] ?? '/forum/programme');
$GLOBALS['forum_test_options'] = array(
    'barnahus_forum_preview_enabled' => $is_development_server || ($argv[2] ?? '0') === '1',
    'barnahus_forum_content_approved' => $is_development_server || ($argv[3] ?? '0') === '1',
    'blog_charset' => 'UTF-8',
);
$GLOBALS['forum_test_editor'] = $is_development_server
    ? isset($_GET['editor']) && '1' === $_GET['editor']
    : ($argv[4] ?? '0') === '1';
$GLOBALS['forum_test_route_registered'] = false;

function get_option($key, $default = false) { return $GLOBALS['forum_test_options'][$key] ?? $default; }
function add_action($hook, $callback) {
    if ('template_redirect' === $hook && 'barnahus_forum_preview_route' === $callback) {
        $GLOBALS['forum_test_route_registered'] = true;
    }
}
function untrailingslashit($value) { return '/' === $value ? '' : rtrim($value, '/'); }
function plugin_dir_path($file) { return trailingslashit(dirname($file)); }
function trailingslashit($value) { return rtrim($value, '/\\') . '/'; }
function status_header() {}
function nocache_headers() {}
function current_user_can() { return $GLOBALS['forum_test_editor']; }

$_SERVER['REQUEST_URI'] = $route;
require dirname(__DIR__) . '/barnahus-site-tools/includes/forum-preview.php';

if (!$GLOBALS['forum_test_route_registered']) {
    echo 'BLOCKED';
    exit;
}

barnahus_forum_preview_route();
echo 'NOT_FOUND';
