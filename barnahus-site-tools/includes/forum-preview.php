<?php

if (!defined('ABSPATH')) {
    exit;
}

if (
    !get_option('barnahus_forum_preview_enabled')
    || !get_option('barnahus_forum_content_approved')
) {
    return;
}

add_action('template_redirect', 'barnahus_forum_preview_route');

function barnahus_forum_preview_route() {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);

    if (!is_string($path)) {
        return;
    }

    $normalised_path = untrailingslashit($path);
    $template_routes = array(
        '/forum/programme' => 'forum-programme-template.php',
        '/forum/participants' => 'forum-participants-template.php',
    );

    if (isset($template_routes[$normalised_path])) {
        barnahus_forum_preview_send_template(
            barnahus_forum_preview_dir() . '/' . $template_routes[$normalised_path]
        );
    }

    $asset_routes = array(
        '/forum/assets/pathway-assets/' => array(
            'england.png' => array('pathway-assets/england.png', 'image/png'),
            'finland.png' => array('pathway-assets/finland.png', 'image/png'),
            'pathways-package.zip' => array('pathway-assets/pathways-package.zip', 'application/zip'),
        ),
        '/forum/assets/notebook/' => array(
            'forum-notebook-cover.jpg' => array('notebook-assets/forum-notebook-cover.jpg', 'image/jpeg'),
            'forum-notebook-dotgrid.jpg' => array('notebook-assets/forum-notebook-dotgrid.jpg', 'image/jpeg'),
        ),
    );

    foreach ($asset_routes as $asset_prefix => $allowed_assets) {
        if (0 !== strpos($normalised_path, $asset_prefix)) {
            continue;
        }

        $asset_name = basename(substr($normalised_path, strlen($asset_prefix)));

        if (!isset($allowed_assets[$asset_name])) {
            return;
        }

        barnahus_forum_preview_send_file(
            barnahus_forum_preview_dir() . '/' . $allowed_assets[$asset_name][0],
            $allowed_assets[$asset_name][1]
        );
    }
}

function barnahus_forum_preview_dir() {
    return plugin_dir_path(dirname(__FILE__)) . 'forum-preview';
}

function barnahus_forum_preview_send_template($file_path) {
    if (!is_readable($file_path)) {
        status_header(404);
        exit;
    }

    status_header(200);
    nocache_headers();
    header('Content-Type: text/html; charset=' . get_option('blog_charset'));
    header('X-Robots-Tag: noindex, nofollow, noarchive', true);

    require $file_path;
    exit;
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
    header('X-Content-Type-Options: nosniff');
    header('X-Robots-Tag: noindex, nofollow, noarchive', true);

    readfile($file_path);
    exit;
}
