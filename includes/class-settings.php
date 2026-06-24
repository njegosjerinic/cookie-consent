<?php

class Settings
{

    public function __construct()
    {
        $this->register_hooks();
    }

    public function register_hooks()
    {
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function register_settings()
    {
        register_setting(
            'njegos_cookie_settings_group',
            'njegos_cookie_settings'
        );
    }

}

$settings = new Settings();