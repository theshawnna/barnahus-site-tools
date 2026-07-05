<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('template_redirect', 'barnahus_forum_preview_route');

function barnahus_forum_preview_route() {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);

    if (!is_string($path)) {
        return;
    }

    $normalised_path = untrailingslashit($path);
    $template_routes = array(
        '/forum/programme' => 'forum-programme-template.html',
        '/forum/participants' => 'forum-participants-template.html',
    );

    if (isset($template_routes[$normalised_path])) {
        barnahus_forum_preview_send_file(
            barnahus_forum_preview_dir() . '/' . $template_routes[$normalised_path],
            'text/html; charset=' . get_option('blog_charset')
        );
    }

    $asset_prefix = '/forum/assets/pathway-assets/';

    if (0 !== strpos($normalised_path, $asset_prefix)) {
        return;
    }

    $asset_name = basename(substr($normalised_path, strlen($asset_prefix)));
    $allowed_assets = array(
        'england.png' => 'image/png',
        'finland.png' => 'image/png',
        'pathways-package.zip' => 'application/zip',
    );

    if (!isset($allowed_assets[$asset_name])) {
        return;
    }

    barnahus_forum_preview_send_file(
        barnahus_forum_preview_dir() . '/pathway-assets/' . $asset_name,
        $allowed_assets[$asset_name]
    );
}

function barnahus_forum_preview_dir() {
    return plugin_dir_path(dirname(__FILE__)) . 'forum-preview';
}

function barnahus_forum_preview_send_file($file_path, $content_type) {
    if (!is_readable($file_path)) {
        status_header(404);
        exit;
    }

    status_header(200);
    nocache_headers();
    header('Content-Type: ' . $content_type);
    header('Content-Length: ' . filesize($file_path));

    readfile($file_path);
    exit;
}
