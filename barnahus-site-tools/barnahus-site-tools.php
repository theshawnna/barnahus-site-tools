<?php
/**
 * Plugin Name: Barnahus Site Tools
 * Description: Custom functionality for barnahus.eu.
 * Version: 1.1.1
 * Author: Barnahus Europe
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'includes/init.php';

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'barnahus-featured-post',
        plugin_dir_url(__FILE__) . 'css/featured-post.css',
        array(),
        '1.1.0'
    );
});
