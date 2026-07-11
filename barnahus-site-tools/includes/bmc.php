<?php

if (!defined('ABSPATH')) {
    exit;
}

function barnahus_bmc_allowed_paths() {
    return array(
        '/about/who-we-are/',
        '/about/vision/',
        '/category/news/',
        '/about/milestones/',
        '/about/contact-us/',
        '/barnahus/about-barnahus/',
        '/barnahus/the-setup-of-barnahus/where-to-start/',
        '/barnahus/the-practice-in-barnahus/standards/',
        '/barnahus/the-practice-in-barnahus/the-multidisciplinary-team/',
        '/barnahus/the-practice-in-barnahus/progress-in-europe/',
        '/membership/current-members/',
        '/library/',
    );
}

function barnahus_bmc_current_path() {
    $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '/';
    $path = wp_parse_url($request_uri, PHP_URL_PATH);

    if (!is_string($path) || '' === $path) {
        return '/';
    }

    return trailingslashit($path);
}

function barnahus_bmc_is_allowed_page() {
    return in_array(barnahus_bmc_current_path(), barnahus_bmc_allowed_paths(), true);
}

function barnahus_remove_bmc_raw_widget_on_disallowed_pages() {
    if (barnahus_bmc_is_allowed_page()) {
        return;
    }

    global $wp_filter;

    if (empty($wp_filter['wp_head']->callbacks) || !is_array($wp_filter['wp_head']->callbacks)) {
        return;
    }

    foreach ($wp_filter['wp_head']->callbacks as $priority => $callbacks) {
        foreach ($callbacks as $registered_callback) {
            $callback = isset($registered_callback['function']) ? $registered_callback['function'] : null;

            if (
                !is_array($callback)
                || !isset($callback[0], $callback[1])
                || !is_object($callback[0])
                || 'Buy_Me_A_Coffee_Admin' !== get_class($callback[0])
                || 'header_widget' !== $callback[1]
            ) {
                continue;
            }

            remove_action('wp_head', $callback, $priority);
            $GLOBALS['barnahus_bmc_raw_widget_removed'] = true;
        }
    }
}
add_action('wp', 'barnahus_remove_bmc_raw_widget_on_disallowed_pages', PHP_INT_MAX);

function barnahus_dequeue_bmc_on_disallowed_pages() {
    if (barnahus_bmc_is_allowed_page()) {
        return;
    }

    global $wp_scripts, $wp_styles;

    $dependency_queues = array(
        'script' => $wp_scripts,
        'style' => $wp_styles,
    );

    foreach ($dependency_queues as $asset_type => $dependencies) {
        if (!is_object($dependencies) || !isset($dependencies->registered)) {
            continue;
        }

        foreach ($dependencies->registered as $handle => $dependency) {
            $source = isset($dependency->src) ? (string) $dependency->src : '';

            if (false === stripos($source, 'buymeacoffee')) {
                continue;
            }

            $GLOBALS['barnahus_bmc_asset_dequeued'] = true;

            if ('script' === $asset_type) {
                wp_dequeue_script($handle);
                wp_deregister_script($handle);
            } else {
                wp_dequeue_style($handle);
                wp_deregister_style($handle);
            }
        }
    }
}
add_action('wp_enqueue_scripts', 'barnahus_dequeue_bmc_on_disallowed_pages', PHP_INT_MAX);

function barnahus_render_bmc_styles() {
    $allowed = barnahus_bmc_is_allowed_page();
    ?>
    <style id="barnahus-bmc-overrides">
        <?php if (!$allowed) : ?>
        #bmc-wbtn,
        #bmc-iframe,
        #bmc-wbtn + div,
        #WidgetFloaterPanels {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }
        <?php else : ?>
        #bmc-wbtn {
            background: #dce0f7 !important;
            background-color: #dce0f7 !important;
        }

        #bmc-wbtn,
        #bmc-wbtn *,
        #bmc-wbtn + div,
        #bmc-wbtn + div * {
            font-family: "PT Serif", serif !important;
        }

        #bmc-wbtn + div {
            font-size: 16px !important;
            line-height: 1.45 !important;
        }
        <?php endif; ?>
    </style>
    <?php
}
add_action('wp_head', 'barnahus_render_bmc_styles', 1);

function barnahus_render_bmc_fallback() {
    if (barnahus_bmc_is_allowed_page() || !empty($GLOBALS['barnahus_bmc_raw_widget_removed'])) {
        return;
    }
    ?>
    <script>
    (function () {
        const selectors = '#bmc-wbtn, #bmc-iframe, #bmc-wbtn + div, #WidgetFloaterPanels';
        const hideWidget = function () {
            document.querySelectorAll(selectors).forEach(function (element) {
                element.hidden = true;
                element.setAttribute('aria-hidden', 'true');
            });
        };

        hideWidget();

        const observer = new MutationObserver(hideWidget);
        observer.observe(document.body, { childList: true, subtree: true });
        window.setTimeout(function () {
            hideWidget();
            observer.disconnect();
        }, 10000);
    }());
    </script>
    <?php
}
add_action('wp_footer', 'barnahus_render_bmc_fallback', 100);
