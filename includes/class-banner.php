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

        wp_add_inline_style(
            'frontend-cookie-consent-style',
            sprintf(
                ':root{--cookie-consenter-button-color:%s;}',
                esc_attr($settings['button_color'])
            )
        );

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
            'Saglasnost za kolačiće',
            'cookie-consenter'
        )
            ?>
            ">
            <h2 id="cookie-consenter-title">
                <?php echo esc_html($content['banner_title'] ?? ''); ?>
            </h2>
            <p class="banner-text">
                <?php echo esc_html($content['banner_text'] ?? ''); ?>
            </p>
            <div class="buttons">
                <button type="button" id="cookie-consenter-accept">
                    <?php echo esc_html($content['accept_text'] ?? 'Prihvati'); ?>
                </button>

                <button type="button" id="cookie-consenter-decline">
                    <?php echo esc_html($content['decline_text'] ?? 'Odbij'); ?>
                </button>

                <button type="button" id="cookie-consenter-manage" aria-controls="cookie-consenter-preferences"
                    aria-expanded="false">
                    <?php esc_html_e('Podesi kolačiće', 'cookie-consenter'); ?>
                </button>
            </div>
            <div id="cookie-consenter-preferences" hidden>
                <fieldset>
                    <h3 class="cookie-preference-title">
                        <?php esc_html_e('Podešavanja kolačića', 'cookie-consenter'); ?>
                    </h3>

                    <label for="cookie-consenter-category-necessary">
                        <input type="checkbox" id="cookie-consenter-category-necessary" checked disabled>
                        <?php esc_html_e('Neophodni', 'cookie-consenter'); ?>
                    </label>

                    <p>
                        <?php esc_html_e(
                            'Neophodni su za rad sajta i ne mogu se isključiti.',
                            'cookie-consenter'
                        ); ?>
                    </p>

                    <label for="cookie-consenter-category-preferences">
                        <input type="checkbox" id="cookie-consenter-category-preferences">
                        <?php esc_html_e('Funkcionalni', 'cookie-consenter'); ?>
                    </label>

                    <p>
                        <?php esc_html_e(
                            'Pamte izbore koji poboljšavaju vaše iskustvo.',
                            'cookie-consenter'
                        ); ?>
                    </p>

                    <label for="cookie-consenter-category-analytics">
                        <input type="checkbox" id="cookie-consenter-category-analytics">
                        <?php esc_html_e('Analitički', 'cookie-consenter'); ?>
                    </label>

                    <p>
                        <?php esc_html_e(
                            'Pomažu nam da razumemo kako posetioci koriste sajt.',
                            'cookie-consenter'
                        ); ?>
                    </p>

                    <label for="cookie-consenter-category-marketing">
                        <input type="checkbox" id="cookie-consenter-category-marketing">
                        <?php esc_html_e('Marketinški', 'cookie-consenter'); ?>
                    </label>

                    <p>
                        <?php esc_html_e(
                            'Omogućavaju oglašavanje i marketinške usluge.',
                            'cookie-consenter'
                        ); ?>
                    </p>
                </fieldset>

                <button type="button" id="cookie-consenter-save-preferences">
                    <?php esc_html_e('Sačuvaj podešavanja', 'cookie-consenter'); ?>
                </button>
            </div>

            <button type="button" id="cookie-consenter-close"
                aria-label="<?php esc_attr_e('Zatvori podešavanja kolačića', 'cookie-consenter'); ?>" hidden>
                <img src="<?php echo esc_url(COOKIE_CONSENTER_URL . 'assets/closing-button.png'); ?>" alt="close button"
                    aria-hidden="true">
            </button>
        </div>

        <button type="button" id="cookie-consenter-settings" aria-controls="cookie-consenter-banner" aria-expanded="false"
            aria-label="<?php esc_attr_e('Podešavanja kolačića', 'cookie-consenter'); ?>">
            <img src="<?php echo esc_url(COOKIE_CONSENTER_URL . 'assets/cookie-settings-image.png'); ?>" alt="" width="48"
                height="48" aria-hidden="true">
        </button>

        <?php

    }

}
