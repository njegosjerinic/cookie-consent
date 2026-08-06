<?php

namespace CookieConsenter;

defined('ABSPATH') || exit;

final class Banner
{

    public function __construct()
    {
        $this->register_hooks();
    }

    public function register_hooks(): void
    {
        add_action('wp_footer', [$this, 'render_banner']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
    }

    public function enqueue_frontend_assets(): void
    {
        wp_enqueue_style(
            'frontend-cookie-consent-style',
            COOKIE_CONSENTER_URL . 'assets/css/frontend.css',
            [],
            COOKIE_CONSENTER_VERSION
        );

        $settings = Settings::get();

        wp_enqueue_script(
            'frontend-cookie-consent-script',
            COOKIE_CONSENTER_URL . 'assets/js/frontend.js',
            [],
            COOKIE_CONSENTER_VERSION,
            true,
        );

        wp_localize_script(
            'frontend-cookie-consent-script',
            'CookieConsenterConfig',
            [
                'policyVersion' => (string) $settings['policy_version'],
                'consentDurationDays' => (int) $settings['consent_duration_days'],
            ],
        );
    }

    public function render_banner(): void
    {
        $content = Settings::get();

        ?>

        <div id="cookie-consenter-banner" role="region" aria-live="polite" hidden aria-label="<?php
        esc_attr_e(
            'Cookie consent',
            'cookie-consenter'
        )
            ?>
            ">
            <p>
                <?php echo esc_html($content['banner_text'] ?? ''); ?>
            </p>

            <button type="button" id="cookie-consenter-accept">
                <?php echo esc_html($content['accept_text'] ?? 'Accept'); ?>
            </button>

            <button type="button" id="cookie-consenter-decline">
                <?php echo esc_html($content['decline_text'] ?? 'Decline'); ?>
            </button>

            <button type="button" id="cookie-consenter-manage" aria-controls="cookie-consenter-preferences"
                aria-expanded="false">
                <?php esc_html_e('Manage preferences', 'cookie-consenter'); ?>
            </button>

            <div id="cookie-consenter-preferences" hidden>
                <fieldset>
                    <legend>
                        <?php esc_html_e('Cookie preferences', 'cookie-consenter'); ?>
                    </legend>

                    <label for="cookie-consenter-category-necessary">
                        <input type="checkbox" id="cookie-consenter-category-necessary" checked disabled>
                        <?php esc_html_e('Necessary', 'cookie-consenter'); ?>
                    </label>

                    <p>
                        <?php esc_html_e(
                            'Required for the website to function and cannot be disabled.',
                            'cookie-consenter'
                        ); ?>
                    </p>

                    <label for="cookie-consenter-category-preferences">
                        <input type="checkbox" id="cookie-consenter-category-preferences">
                        <?php esc_html_e('Preferences', 'cookie-consenter'); ?>
                    </label>

                    <p>
                        <?php esc_html_e(
                            'Remembers choices that improve your experience.',
                            'cookie-consenter'
                        ); ?>
                    </p>

                    <label for="cookie-consenter-category-analytics">
                        <input type="checkbox" id="cookie-consenter-category-analytics">
                        <?php esc_html_e('Analytics', 'cookie-consenter'); ?>
                    </label>

                    <p>
                        <?php esc_html_e(
                            'Helps us understand how visitors use the website.',
                            'cookie-consenter'
                        ); ?>
                    </p>

                    <label for="cookie-consenter-category-marketing">
                        <input type="checkbox" id="cookie-consenter-category-marketing">
                        <?php esc_html_e('Marketing', 'cookie-consenter'); ?>
                    </label>

                    <p>
                        <?php esc_html_e(
                            'Allows advertising and marketing-related services.',
                            'cookie-consenter'
                        ); ?>
                    </p>
                </fieldset>

                <button type="button" id="cookie-consenter-save-preferences">
                    <?php esc_html_e('Save preferences', 'cookie-consenter'); ?>
                </button>
            </div>

            <button type="button" id="cookie-consenter-close"
                aria-label="<?php esc_attr_e('Close cookie settings', 'cookie-consenter'); ?>" hidden>
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <button type="button" id="cookie-consenter-settings" aria-controls="cookie-consenter-banner" aria-expanded="false">
            <?php esc_html_e('Cookie settings', 'cookie-consenter'); ?>
        </button>

        <?php

    }

}
