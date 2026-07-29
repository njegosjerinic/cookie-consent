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

        wp_enqueue_script(
            'frontend-cookie-consent-script',
            COOKIE_CONSENTER_URL . 'assets/js/frontend.js',
            [],
            COOKIE_CONSENTER_VERSION,
            true
        );
    }

    public function render_banner(): void
    {
        $content = Settings::get();

        ?>

        <div id="cookie-consenter-banner" role="region" aria-live="polite" aria-label="<?php
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
        </div>

        <?php

    }

}
