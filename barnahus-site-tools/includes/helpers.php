<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('barnahus_newsletter_tracked_url')) {
    function barnahus_newsletter_tracked_url($destination_url, $campaign, $content) {
        $clean_url = remove_query_arg(
            array('utm_source', 'utm_medium', 'utm_campaign', 'utm_content'),
            $destination_url
        );

        return add_query_arg(
            array(
                'utm_source'   => 'newsletter',
                'utm_medium'   => 'email',
                'utm_campaign' => sanitize_title($campaign),
                'utm_content'  => sanitize_title($content),
            ),
            $clean_url
        );
    }
}
