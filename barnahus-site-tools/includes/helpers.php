<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('barnahus_newsletter_existing_utm_keys')) {
    function barnahus_newsletter_existing_utm_keys($url) {
        $query = wp_parse_url($url, PHP_URL_QUERY);

        if (empty($query)) {
            return array();
        }

        $keys = array();

        foreach (explode('&', $query) as $part) {
            if ($part === '') {
                continue;
            }

            $key = rawurldecode(explode('=', $part, 2)[0]);

            if (preg_match('/^utm_/i', $key)) {
                $keys[] = $key;
            }
        }

        return array_values(array_unique($keys));
    }
}

if (!function_exists('barnahus_newsletter_tracked_url')) {
    function barnahus_newsletter_tracked_url($destination_url, $campaign, $content) {
        $destination_url = trim((string) $destination_url);
        $campaign = sanitize_title($campaign);
        $content = sanitize_title($content);

        if ($destination_url === '' || $campaign === '' || $content === '') {
            return '';
        }

        $clean_url = remove_query_arg(barnahus_newsletter_existing_utm_keys($destination_url), $destination_url);

        return add_query_arg(
            array(
                'utm_source'   => 'newsletter',
                'utm_medium'   => 'email',
                'utm_campaign' => $campaign,
                'utm_content'  => $content,
            ),
            $clean_url
        );
    }
}
