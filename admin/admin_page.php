<?php
if (!defined('ABSPATH')) {
    exit; 
}

function licenser_core_admin_page() {
    ?>
    <div class="wrap">
        <h1>Licenser Core</h1>
        <h2 class="nav-tab-wrapper">
            <a href="#welcome" class="nav-tab nav-tab-active" onclick="openTab(event, 'welcome')">Welcome</a>
            <a href="#settings" class="nav-tab" onclick="openTab(event, 'settings')">Settings</a>
            <a href="#clients" class="nav-tab" onclick="openTab(event, 'clients')">Clients</a>
            <a href="#about" class="nav-tab" onclick="openTab(event, 'about')">About</a>
        </h2>
        <?php
        include_once LC_PLUGIN_DIR . 'includes/pages/home.php';
        include_once LC_PLUGIN_DIR . 'includes/pages/settings.php';
        include_once LC_PLUGIN_DIR . 'includes/pages/clients.php';
        include_once LC_PLUGIN_DIR . 'includes/pages/about.php';
        ?>
    </div>
    <?php
}