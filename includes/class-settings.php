<?php

namespace CookieConsenter;

defined('ABSPATH') || exit;


final class Settings
{
    public const OPTION_NAME = 'cookie_consenter_settings';
    public const SETTINGS_GROUP = 'cookie_consenter_settings_group';

    // Ova funkcija vraca defaultne vrijednosti polja 
    public static function defaults(): array
    {
        return [
            'banner_title' => __('Vaša privatnost je važna', 'cookie-consenter'),
            'banner_text' => __(
                'Koristimo kolačiće kako bismo poboljšali vaše iskustvo',
                'cookie-consenter'
            ),
            'accept_text' => __('Prihvati', 'cookie-consenter'),
            'decline_text' => __('Odbij', 'cookie-consenter'),
            'button_color' => '#2563eb',
            'policy_version' => '1',
            'consent_duration_days' => 180
        ];
    }

    //Ova funkcija uzima rezultate iz polja i puni ih defaultima ako nemaju neka mjesta popunjena 
    public static function get(): array
    {
        $settings = get_option(self::OPTION_NAME, []);

        if (!is_array($settings)) {
            $settings = [];
        }

        $defaults = self::defaults();
        $settings = wp_parse_args($settings, $defaults);

        $legacy_defaults = [
            'banner_text' => 'We use cookies to improve your experience',
            'accept_text' => 'Accept',
            'decline_text' => 'Decline',
        ];

        foreach ($legacy_defaults as $key => $legacy_value) {
            if ($settings[$key] === $legacy_value) {
                $settings[$key] = $defaults[$key];
            }
        }

        return $settings;
    }

    //Registrovanje hukova 
    public function __construct()
    {
        $this->register_hooks();
    }

    public function register_hooks(): void
    {
        add_action('admin_init', [$this, 'register_settings']);
    }

    //Registrovanje podesavanja 
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

    //Ciscenje podesavanja 
    public function sanitize_settings(mixed $input): array
    {
        $defaults = self::defaults();

        if (!is_array($input)) {
            return $defaults;
        }

        return [
            'banner_title' => sanitize_text_field(
                $input['banner_title'] ?? $defaults['banner_title']
            ),
            'banner_text' => sanitize_textarea_field($input['banner_text'] ?? $defaults['banner_text']),
            'accept_text' => sanitize_text_field($input['accept_text'] ?? $defaults['accept_text']),
            'decline_text' => sanitize_text_field($input['decline_text'] ?? $defaults['decline_text']),
            'button_color' => sanitize_hex_color($input['button_color'] ?? '')
                ?: $defaults['button_color'],
            'policy_version' => sanitize_text_field(
                $input['policy_version'] ?? $defaults['policy_version']
            ),
            'consent_duration_days' => min(
                3650,
                max(
                    1,
                    absint(
                        $input['consent_duration_days'] ?? $defaults['consent_duration_days']
                    )
                )
            )
        ];
    }

}
