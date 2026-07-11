<?php

define('ABSPATH', __DIR__ . '/');

function add_action() {}
function wp_unslash($value) { return $value; }
function wp_parse_url($value, $component) { return parse_url($value, $component); }
function trailingslashit($value) { return '/' === $value ? '/' : rtrim($value, '/') . '/'; }

$GLOBALS['test_dequeued_scripts'] = array();
$GLOBALS['test_deregistered_scripts'] = array();
$GLOBALS['test_dequeued_styles'] = array();
$GLOBALS['test_deregistered_styles'] = array();

function wp_dequeue_script($handle) { $GLOBALS['test_dequeued_scripts'][] = $handle; }
function wp_deregister_script($handle) { $GLOBALS['test_deregistered_scripts'][] = $handle; }
function wp_dequeue_style($handle) { $GLOBALS['test_dequeued_styles'][] = $handle; }
function wp_deregister_style($handle) { $GLOBALS['test_deregistered_styles'][] = $handle; }
function remove_action($hook, $callback, $priority) {
    $GLOBALS['test_removed_actions'][] = array($hook, $callback, $priority);
}

class Buy_Me_A_Coffee_Admin {
    public function header_widget() {}
}

require dirname(__DIR__) . '/barnahus-site-tools/includes/bmc.php';

function bmc_assert($condition, $message) {
    if ($condition) {
        return;
    }

    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

$GLOBALS['wp_scripts'] = (object) array(
    'registered' => array(
        'buy-me-a-coffee' => (object) array('src' => '/wp-content/plugins/buymeacoffee/public/js/buy-me-a-coffee-public.js'),
        'site-navigation' => (object) array('src' => '/wp-content/themes/barnahus/navigation.js'),
    ),
);
$GLOBALS['wp_styles'] = (object) array(
    'registered' => array(
        'buy-me-a-coffee' => (object) array('src' => '/wp-content/plugins/buymeacoffee/public/css/buy-me-a-coffee-public.css'),
        'site-style' => (object) array('src' => '/wp-content/themes/barnahus/style.css'),
    ),
);

$_SERVER['REQUEST_URI'] = '/?utm_source=test';
$bmc_admin = new Buy_Me_A_Coffee_Admin();
$GLOBALS['wp_filter']['wp_head'] = (object) array(
    'callbacks' => array(
        10 => array(
            'buy-me-a-coffee-widget' => array(
                'function' => array($bmc_admin, 'header_widget'),
            ),
        ),
    ),
);
$GLOBALS['test_removed_actions'] = array();

barnahus_remove_bmc_raw_widget_on_disallowed_pages();
barnahus_dequeue_bmc_on_disallowed_pages();

bmc_assert(1 === count($GLOBALS['test_removed_actions']), 'The raw Buy Me a Coffee wp_head callback should be removed on disallowed pages.');
bmc_assert('wp_head' === $GLOBALS['test_removed_actions'][0][0], 'Only the Buy Me a Coffee wp_head callback should be removed.');
bmc_assert(array('buy-me-a-coffee') === $GLOBALS['test_dequeued_scripts'], 'The local Buy Me a Coffee script should be dequeued on disallowed pages.');
bmc_assert(array('buy-me-a-coffee') === $GLOBALS['test_deregistered_scripts'], 'The local Buy Me a Coffee script should be deregistered on disallowed pages.');
bmc_assert(array('buy-me-a-coffee') === $GLOBALS['test_dequeued_styles'], 'The local Buy Me a Coffee stylesheet should be dequeued on disallowed pages.');
bmc_assert(array('buy-me-a-coffee') === $GLOBALS['test_deregistered_styles'], 'The local Buy Me a Coffee stylesheet should be deregistered on disallowed pages.');

ob_start();
barnahus_render_bmc_fallback();
$suppressed_fallback = ob_get_clean();
bmc_assert('' === $suppressed_fallback, 'The browser fallback should be skipped after registered assets are removed.');

unset($GLOBALS['barnahus_bmc_raw_widget_removed']);
ob_start();
barnahus_render_bmc_fallback();
$raw_embed_fallback = ob_get_clean();
bmc_assert(false !== strpos($raw_embed_fallback, 'MutationObserver'), 'A bounded fallback should remain for raw embeds.');

$_SERVER['REQUEST_URI'] = '/library/?ref=test';
$before_allowed_scripts = count($GLOBALS['test_dequeued_scripts']);
barnahus_dequeue_bmc_on_disallowed_pages();
bmc_assert($before_allowed_scripts === count($GLOBALS['test_dequeued_scripts']), 'Allowed pages should retain Buy Me a Coffee assets.');

echo "Buy Me a Coffee loading checks passed.\n";
