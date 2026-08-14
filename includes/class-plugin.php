<?php

namespace CookieConsenter;

defined('ABSPATH') || exit;

/**
 * Coordinates the plugin components.
 */
final class Plugin
{
    /**
     * Register all plugin components.
     */
    public function run(): void
    {
        new Settings();
        new Admin();
        new Consent();
        new Banner();
    }
}
