<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Featured Post Shortcode
 *
 * Usage:
 *
 * Basic:
 * [featured_post id="7751"]
 *
 * Specify a heading level:
 * [featured_post id="7751" heading="h2"]
 *
 * Available heading levels:
 * h1, h2, h3, h4, h5, h6
 *
 * Parameters:
 * - id      (required) The ID of the post or page to display.
 * - heading (optional) The HTML heading level. Defaults to h3.
 *
 * Examples:
 *
 * [featured_post id="7751"]
 * [featured_post id="7751" heading="h1"]
 * [featured_post id="7751" heading="h2"]
 * [featured_post id="7751" heading="h4"]
 *
 * The card uses the site's existing heading styles, so selecting a
 * different heading level will automatically match the site's typography.
 */
function barnahus_featured_post_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'id' => '',
            'heading' => 'h3',
        ),
        $atts,
        'featured_post'
    );

    $post_id = absint($atts['id']);
    $allowed_headings = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6');
    $heading = strtolower($atts['heading']);

    if (!in_array($heading, $allowed_headings, true)) {
        $heading = 'h3';
    }

    if (!$post_id) {
        return '';
    }

    $post = get_post($post_id);

    if (!$post || $post->post_status !== 'publish') {
        return '';
    }

    $title = get_the_title($post_id);
    $url = get_permalink($post_id);
    $excerpt = has_excerpt($post_id)
        ? get_the_excerpt($post_id)
        : wp_trim_words(wp_strip_all_tags($post->post_content), 35);

    barnahus_enqueue_featured_post_assets();

    ob_start();
    ?>

    <div class="bh-featured-post-card">
        <div class="bh-featured-post-content">
            <<?php echo esc_html($heading); ?>>
                <a href="<?php echo esc_url($url); ?>">
                    <?php echo esc_html($title); ?>
                </a>
            </<?php echo esc_html($heading); ?>>

            <p><?php echo esc_html($excerpt); ?></p>

            <a class="bh-featured-post-button" href="<?php echo esc_url($url); ?>">
                Read more
            </a>
        </div>
    </div>

    <?php
    return ob_get_clean();
}

function barnahus_enqueue_featured_post_assets() {
    $stylesheet_path = dirname(__DIR__) . '/css/featured-post.css';
    $version = file_exists($stylesheet_path)
        ? (string) filemtime($stylesheet_path)
        : BARNAHUS_SITE_TOOLS_VERSION;

    wp_enqueue_style(
        'barnahus-featured-post',
        plugin_dir_url(dirname(__FILE__)) . 'css/featured-post.css',
        array(),
        $version
    );
}

add_shortcode('featured_post', 'barnahus_featured_post_shortcode');
