<?php

if (!defined('ABSPATH')) {
    exit;
}

$barnahus_required_modules = array(
    '/helpers.php',
    '/featured-post.php',
    '/events.php',
    '/bmc.php',
    '/settings.php',
);

foreach ($barnahus_required_modules as $barnahus_module) {
    require_once plugin_dir_path(__FILE__) . $barnahus_module;
}

$barnahus_optional_modules = array(
    '/forum-preview.php',
);

foreach ($barnahus_optional_modules as $barnahus_module) {
    $barnahus_module_path = plugin_dir_path(__FILE__) . $barnahus_module;

    if (is_readable($barnahus_module_path)) {
        require_once $barnahus_module_path;
    }
}
