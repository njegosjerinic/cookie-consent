<?php



class Admin
{

    public function __construct()
    {
        $this->register_hooks();
    }

    public function register_hooks()
    {
        add_action('admin_menu', [$this, 'plugin_register_admin_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function render_page()
    { ?>
        <div class="wrap">
            <h1>Cookie Consent Settings</h1>
            <p>Welcome to Cookie Consent Plugin</p>
        </div>

        <form method="post" action="options.php">
            <?php
            $settings = get_option('njegos_cookie_settings');
            settings_fields(
                'njegos_cookie_settings_group',
            );
            ?>

            <span>Banner text</span>
            <input type="text" value="<?php echo $settings['banner_text'] ?? '' ?>" name="njegos_cookie_settings[banner_text]">
            <span>Accept text</span>
            <input type="text" value="<?php echo $settings['accept_text'] ?? '' ?>" name="njegos_cookie_settings[accept_text]">
            <span>Decline text</span>
            <input type="text" value="<?php echo $settings['decline_text'] ?? '' ?>"
                name="njegos_cookie_settings[decline_text]">
            <button type="submit">Sacuvaj podesavanja</button>
        </form>


        <?php
    }

    public function enqueue_admin_assets()
    {
        wp_enqueue_style(
            'admin-cookie-consent-style',
            NJEGOS_COOKIE_PLUGIN_URL . 'assets/css/admin.css',
            [],
            '1.0'
        );

        wp_enqueue_script(
            'admin-cookie-consent-script',
            NJEGOS_COOKIE_PLUGIN_URL . 'assets/js/admin.js',
            [],
            '1.0',
            true
        );
    }


    public function plugin_register_admin_page()
    {
        add_menu_page(
            __('Cookie Consent Settings', 'cookie-consent-textdomain'),
            'Cookie consent Plugin',
            'manage_options',
            'cookie-consent-settings',
            [$this, 'render_page']
        );

    }

}

$admin = new Admin();
