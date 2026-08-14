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
                    'Nemate dozvolu za pristup ovoj stranici.',
                    'cookie-consenter'
                )
            );
        }

        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Podešavanja saglasnosti za kolačiće', 'cookie-consenter') ?></h1>
            <p><?php esc_html_e('Podesite tekst prikazan u obaveštenju o kolačićima', 'cookie-consenter') ?></p>


            <form method="post" action="options.php">
                <?php
                $settings = Settings::get();
                settings_fields(
                    Settings::SETTINGS_GROUP,
                );
                ?>

                <p class="cookie-consenter-field">
                    <label for="cookie-consenter-banner-title">
                        <?php esc_html_e('Naslov obaveštenja', 'cookie-consenter'); ?>
                    </label>
                    <input type="text" id="cookie-consenter-banner-title"
                        name="<?php echo esc_attr(Settings::OPTION_NAME) ?>[banner_title]"
                        value="<?php echo esc_attr($settings['banner_title'] ?? '') ?>">
                </p>

                <p class="cookie-consenter-field">
                    <label for="cookie-consenter-banner-text">
                        <?php esc_html_e('Opis obaveštenja', 'cookie-consenter'); ?>
                    </label>
                    <textarea id="cookie-consenter-banner-text" rows="5"
                        name="<?php echo esc_attr(Settings::OPTION_NAME) ?>[banner_text]"><?php
                        echo esc_textarea($settings['banner_text'] ?? '');
                        ?></textarea>
                </p>
                <p>
                    <label for="cookie-consenter-accept-text">
                        <?php esc_html_e('Tekst dugmeta za prihvatanje', 'cookie-consenter'); ?>
                    </label>
                    <input type="text" id="cookie-consenter-accept-text"
                        name="<?php echo esc_attr(Settings::OPTION_NAME) ?>[accept_text]"
                        value="<?php echo esc_attr($settings['accept_text'] ?? '') ?>">
                </p>
                <p>
                    <label for="cookie-consenter-decline-text">
                        <?php esc_html_e('Tekst dugmeta za odbijanje', 'cookie-consenter'); ?>
                    </label>
                    <input type="text" id="cookie-consenter-decline-text"
                        name="<?php echo esc_attr(Settings::OPTION_NAME) ?>[decline_text]"
                        value="<?php echo esc_attr($settings['decline_text'] ?? '') ?>">
                </p>

                <p>
                    <label for="cookie-consenter-button-color">
                        <?php esc_html_e('Boja dugmadi', 'cookie-consenter'); ?>
                    </label>
                    <input type="color" id="cookie-consenter-button-color"
                        name="<?php echo esc_attr(Settings::OPTION_NAME); ?>[button_color]"
                        value="<?php echo esc_attr($settings['button_color'] ?? '#2563eb'); ?>">
                </p>

                <p>
                    <label for="cookie-consenter-policy-version">
                        <?php esc_html_e('Verzija politike', 'cookie-consenter'); ?>
                    </label>

                    <input type="text" id="cookie-consenter-policy-version"
                        name="<?php echo esc_attr(Settings::OPTION_NAME); ?>[policy_version]"
                        value="<?php echo esc_attr($settings['policy_version'] ?? '1'); ?>">

                    <span class="description">
                        <?php esc_html_e(
                            'Promenite ovu vrednost da biste ponovo zatražili saglasnost posetilaca.',
                            'cookie-consenter'
                        ); ?>
                    </span>
                </p>

                <p>
                    <label for="cookie-consenter-consent-duration">
                        <?php esc_html_e('Trajanje saglasnosti u danima', 'cookie-consenter'); ?>
                    </label>

                    <input type="number" id="cookie-consenter-consent-duration"
                        name="<?php echo esc_attr(Settings::OPTION_NAME); ?>[consent_duration_days]" value="<?php echo esc_attr(
                               (string) ($settings['consent_duration_days'] ?? 180)
                           ); ?>" min="1" max="3650" step="1">

                    <span class="description">
                        <?php esc_html_e(
                            'Koliko dugo sačuvana odluka o saglasnosti ostaje važeća.',
                            'cookie-consenter'
                        ); ?>
                    </span>
                </p>

                <?php submit_button(__('Sačuvaj podešavanja', 'cookie-consenter')); ?>

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
            __('Podešavanja saglasnosti za kolačiće', 'cookie-consenter'),
            __('Saglasnost za kolačiće', 'cookie-consenter'),
            'manage_options',
            'cookie-consenter-settings',
            [$this, 'render_page']
        );

    }

}

