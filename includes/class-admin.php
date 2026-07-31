<?php



namespace CookieConsenter;

defined('ABSPATH') || exit;

final class Admin
{
    private string $page_hook = '';

    public function __construct()
    {
        $this->register_hooks();
    }

    public function register_hooks(): void
    {
        add_action('admin_menu', [$this, 'plugin_register_admin_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function render_page(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(
                esc_html__(
                    'You do not have permission to access this page.',
                    'cookie-consenter'
                )
            );
        }

        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Cookie Consenter Settings', 'cookie-consenter') ?></h1>
            <p><?php esc_html_e('Configure the text displayed in the cookie consent banner', 'cookie-consenter') ?></p>


            <form method="post" action="options.php">
                <?php
                $settings = Settings::get();
                settings_fields(
                    Settings::SETTINGS_GROUP,
                );
                ?>

                <p>
                    <label for="cookie-consenter-banner-text">
                        <?php esc_html_e('Banner text', 'cookie-consenter'); ?>
                    </label>
                    <input type="text" id="cookie-consenter-banner-text"
                        name="<?php echo esc_attr(Settings::OPTION_NAME) ?>[banner_text]"
                        value="<?php echo esc_attr($settings['banner_text'] ?? '') ?>">
                </p>
                <p>
                    <label for="cookie-consenter-accept-text">
                        <?php esc_html_e('Accept button text', 'cookie-consenter'); ?>
                    </label>
                    <input type="text" id="cookie-consenter-accept-text"
                        name="<?php echo esc_attr(Settings::OPTION_NAME) ?>[accept_text]"
                        value="<?php echo esc_attr($settings['accept_text'] ?? '') ?>">
                </p>
                <p>
                    <label for="cookie-consenter-decline-text">
                        <?php esc_html_e('Decline button text', 'cookie-consenter'); ?>
                    </label>
                    <input type="text" id="cookie-consenter-decline-text"
                        name="<?php echo esc_attr(Settings::OPTION_NAME) ?>[decline_text]"
                        value="<?php echo esc_attr($settings['decline_text'] ?? '') ?>">
                </p>

                <p>
                    <label for="cookie-consenter-policy-version">
                        <?php esc_html_e('Policy version', 'cookie-consenter'); ?>
                    </label>

                    <input type="text" id="cookie-consenter-policy-version"
                        name="<?php echo esc_attr(Settings::OPTION_NAME); ?>[policy_version]"
                        value="<?php echo esc_attr($settings['policy_version'] ?? '1'); ?>">

                    <span class="description">
                        <?php esc_html_e(
                            'Change this value to ask visitors for consent again.',
                            'cookie-consenter'
                        ); ?>
                    </span>
                </p>

                <p>
                    <label for="cookie-consenter-consent-duration">
                        <?php esc_html_e('Consent duration in days', 'cookie-consenter'); ?>
                    </label>

                    <input type="number" id="cookie-consenter-consent-duration"
                        name="<?php echo esc_attr(Settings::OPTION_NAME); ?>[consent_duration_days]" value="<?php echo esc_attr(
                               (string) ($settings['consent_duration_days'] ?? 180)
                           ); ?>" min="1" max="3650" step="1">

                    <span class="description">
                        <?php esc_html_e(
                            'How long a saved consent decision remains valid.',
                            'cookie-consenter'
                        ); ?>
                    </span>
                </p>

                <?php submit_button(__('Save settings', 'cookie-consenter')); ?>

            </form>
        </div>

        <?php
    }

    public function enqueue_admin_assets(string $hook_suffix): void
    {
        if ($hook_suffix !== $this->page_hook) {
            return;
        }

        wp_enqueue_style(
            'admin-cookie-consent-style',
            COOKIE_CONSENTER_URL . 'assets/css/admin.css',
            [],
            COOKIE_CONSENTER_VERSION
        );

        wp_enqueue_script(
            'admin-cookie-consent-script',
            COOKIE_CONSENTER_URL . 'assets/js/admin.js',
            [],
            COOKIE_CONSENTER_VERSION,
            true
        );
    }


    public function plugin_register_admin_page(): void
    {
        $this->page_hook = add_menu_page(
            __('Cookie Consenter Settings', 'cookie-consenter'),
            __('Cookie Consenter', 'cookie-consenter'),
            'manage_options',
            'cookie-consenter-settings',
            [$this, 'render_page']
        );

    }

}

