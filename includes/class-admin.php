<?php

class Admin
{

    function __construct()
    {
    }

    function register_hooks()
    {
    }


    function plugin_register_admin_page()
    {
        add_menu_page(
            __('Cookie Consent Settings', 'cookie-consent-textdomain'),
            'Cookie consent Plugin',
            'manage_options',
            'cookie-consent-settings'
        );

    }

}