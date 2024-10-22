<?php
if (!defined('ABSPATH')) {
    exit; 
}

if (!function_exists('licenser_core_menu')) {
    function licenser_core_menu() {
        add_menu_page(
            'Licenser',
            'Licenser',
            'manage_options',
            'licenser-core',
            'licenser_core_admin_page',
            plugin_dir_url(__FILE__) . '../assets/img/logo.svg',
            135
        );

        // Agregar submenús
        add_submenu_page(
            'licenser-core',
            'Licencias',
            'Licencias',
            'manage_options',
            'licenses',
            'licenser_core_licenses_page'
        );

        add_submenu_page(
            'licenser-core',
            'Clientes',
            'Clientes',
            'manage_options',
            'clients',
            'licenser_core_clients_page'
        );
    }
}

add_action('admin_menu', 'licenser_core_menu');

include_once plugin_dir_path(__FILE__) . 'pages/licenses.php';
include_once plugin_dir_path(__FILE__) . 'pages/clients.php';