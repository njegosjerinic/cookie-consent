<?php

namespace CookieConsenter;

defined('ABSPATH') || exit;

/**
 * Converts registered WordPress scripts into inert consent placeholders.
 */
final class Consent
{
    private const CATEGORIES = ['preferences', 'analytics', 'marketing'];

    public function __construct()
    {
        add_filter('script_loader_tag', [$this, 'filter_script_tag'], 10, 3);
    }

    /**
     * Block a registered script when its handle has been assigned a category.
     *
     * Developers assign handles with the cookie_consenter_script_categories
     * filter. Unregistered handles pass through unchanged.
     */
    public function filter_script_tag(string $tag, string $handle, string $src): string
    {
        if (is_admin()) {
            return $tag;
        }

        $scripts = apply_filters('cookie_consenter_script_categories', []);

        if (!is_array($scripts) || !isset($scripts[$handle])) {
            return $tag;
        }

        $category = sanitize_key((string) $scripts[$handle]);

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
}
