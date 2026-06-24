<?php

class Banner
{

    public function __construct()
    {
        $this->register_hooks();
    }

    public function register_hooks()
    {
        add_action('wp_footer', [$this, 'render_banner']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
    }

    public function enqueue_frontend_assets()
    {
        wp_enqueue_style(
            'frontend-cookie-consent-style',
            NJEGOS_COOKIE_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            '1.0'
        );

        wp_enqueue_script(
            'frontend-cookie-consent-script',
            NJEGOS_COOKIE_PLUGIN_URL . 'assets/js/frontend.js',
            [],
            '1.0',
            true
        );
    }

    public function render_banner()
    {
        $content = get_option('njegos_cookie_settings');

        ?>

        <div id="banner">
            <p><?php echo $content['banner_text'] ?? '' ?></p>
            <button id="cookieAccept"><?php echo $content['accept_text'] ?? 'Accept' ?></button>
            <button id="cookieDecline"><?php echo $content['decline_text'] ?? 'Decline' ?></button>
        </div>

        <?php

    }

}

$banner = new Banner();