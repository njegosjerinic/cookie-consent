<?php

namespace CookieConsenter;

defined('ABSPATH') || exit;


final class Settings
{
    public const OPTION_NAME = 'cookie_consenter_settings';
    public const SETTINGS_GROUP = 'cookie_consenter_settings_group';

    public static function defaults(): array
    {
        return [
            'banner_text' => __(
                'We use cookies to improve your experience',
                'cookie-consenter'
            ),
            'accept_text' => __('Accept', 'cookie-consenter'),
            'decline_text' => __('Decline', 'cookie-consenter')
        ];
    }

    public static function get(): array
    {
        $settings = get_option(self::OPTION_NAME, []);

        if (!is_array($settings)) {
            $settings = [];
        }

        return wp_parse_args($settings, self::defaults());
    }

    public function __construct()
    {
        $this->register_hooks();
    }

    public function register_hooks(): void
    {
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function register_settings(): void
    {
        register_setting(
            self::SETTINGS_GROUP,
            self::OPTION_NAME,
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize_settings'],
                'default' => self::defaults(),
            ]
        );
    }

    public function sanitize_settings(mixed $input): array
    {
        $defaults = self::defaults();

        if (!is_array($input)) {
            return $defaults;
        }

        return [
            'banner_text' => sanitize_text_field($input['banner_text'] ?? $defaults['banner_text']),
            'accept_text' => sanitize_text_field($input['accept_text'] ?? $defaults['accept_text']),
            'decline_text' => sanitize_text_field($input['decline_text'] ?? $defaults['decline_text']),
        ];
    }

}
