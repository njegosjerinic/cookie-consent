<?php

/**
 * Plugin Name: Cookie consent plugin
 * Description: Cookie consent plugin
 * Version: 1.0.0
 * Author: Njegos Jerinic
 */

if (!defined('ABSPATH')) {
    exit;
}

define('NJEGOS_COOKIE_PLUGIN_VERSION', '1.0.0');
define('NJEGOS_COOKIE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('NJEGOS_COOKIE_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once(NJEGOS_COOKIE_PLUGIN_DIR . 'includes\class-admin.php');

require_once(NJEGOS_COOKIE_PLUGIN_DIR . 'includes\class-settings.php');

require_once(NJEGOS_COOKIE_PLUGIN_DIR . 'includes\class-banner.php');