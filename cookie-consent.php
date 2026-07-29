<?php
/**
 * Plugin Name:       Cookie Consenter
 * Description:       A lightweight cookie consent and preference manager for WordPress.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.1
 * Author:            Njegoš Jerinić
 * Author URI:        https://njegosjerinic.vercel.app/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       cookie-consenter
 * Domain Path:       /languages
 */

defined('ABSPATH') || exit;

define('COOKIE_CONSENTER_VERSION', '1.0.0');
define('COOKIE_CONSENTER_PATH', plugin_dir_path(__FILE__));
define('COOKIE_CONSENTER_URL', plugin_dir_url(__FILE__));

require_once COOKIE_CONSENTER_PATH . 'includes/class-admin.php';
require_once COOKIE_CONSENTER_PATH . 'includes/class-settings.php';
require_once COOKIE_CONSENTER_PATH . 'includes/class-banner.php';
require_once COOKIE_CONSENTER_PATH . 'includes/class-plugin.php';

add_action(
    'plugins_loaded',
    static function (): void {
        $plugin = new \CookieConsenter\Plugin();
        $plugin->run();
    }
);