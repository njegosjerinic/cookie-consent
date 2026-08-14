<?php

namespace CookieConsenter;

defined('ABSPATH') || exit;

/**
 * Converts registered WordPress scripts into inert consent placeholders.
 */
final class Consent
{
    private const CATEGORIES = ['preferences', 'analytics', 'marketing'];

    private const AUTO_DETECT_PATTERNS = [
        'analytics' => [
            'google-analytics.com/analytics.js',
            'googletagmanager.com/gtag/js?id=g-',
            'plausible.io/js/',
            'cdn.usefathom.com/script.js',
            'clarity.ms/tag/',
        ],
        'marketing' => [
            'googletagmanager.com/gtm.js',
            'googletagmanager.com/gtag/js?id=aw-',
            'connect.facebook.net/',
            'static.hotjar.com/',
            'analytics.tiktok.com/',
            'snap.licdn.com/li.lms-analytics/',
            's.pinimg.com/ct/core.js',
            'js.hs-scripts.com/',
        ],
    ];

    public function __construct()
    {
        add_filter('script_loader_tag', [$this, 'filter_script_tag'], 10, 3);
    }

    /**
     * Block a manually categorized or recognized tracking script.
     *
     * Developers assign handles with the cookie_consenter_script_categories
     * filter. Known tracker URLs are categorized automatically. Manual handle
     * assignments take precedence and unknown scripts pass through unchanged.
     */
    public function filter_script_tag(string $tag, string $handle, string $src): string
    {
        if (is_admin()) {
            return $tag;
        }

        $scripts = apply_filters('cookie_consenter_script_categories', []);

        $category = is_array($scripts) && isset($scripts[$handle])
            ? sanitize_key((string) $scripts[$handle])
            : $this->detect_category($src);

        if ($category === null) {
            return $tag;
        }

        if (!in_array($category, self::CATEGORIES, true)) {
            $category = 'invalid';
        }

        $blocked_tag = preg_replace(
            '/\ssrc=([' . "'\"" . '])(.*?)\1/i',
            ' data-src=$1$2$1',
            $tag,
            1
        );

        if (!is_string($blocked_tag) || $blocked_tag === $tag) {
            $blocked_tag = sprintf(
                '<script data-src="%s"></script>',
                esc_url($src)
            );
        }

        $original_type = '';

        if (preg_match('/\stype=([' . "'\"" . '])(.*?)\1/i', $blocked_tag, $matches) === 1) {
            $original_type = $matches[2];
            $blocked_tag = preg_replace(
                '/\stype=([' . "'\"" . '])(.*?)\1/i',
                ' type="text/plain"',
                $blocked_tag,
                1
            );
        } else {
            $blocked_tag = preg_replace('/<script\b/i', '<script type="text/plain"', $blocked_tag, 1);
        }

        $attributes = sprintf(
            ' data-cookie-consenter-script data-cookie-consenter-category="%s" data-cookie-consenter-state="blocked"',
            esc_attr($category)
        );

        if ($original_type !== '' && strtolower($original_type) !== 'text/javascript') {
            $attributes .= sprintf(
                ' data-cookie-consenter-type="%s"',
                esc_attr($original_type)
            );
        }

        $blocked_tag = preg_replace('/<script\b/i', '<script' . $attributes, $blocked_tag, 1);

        return is_string($blocked_tag) ? $blocked_tag : $tag;
    }

    private function detect_category(string $src): ?string
    {
        $normalized_src = strtolower($src);

        foreach (self::AUTO_DETECT_PATTERNS as $category => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_contains($normalized_src, $pattern)) {
                    return $category;
                }
            }
        }

        return null;
    }
}
