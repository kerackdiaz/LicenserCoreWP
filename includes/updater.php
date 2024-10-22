<?php

add_filter('pre_set_site_transient_update_plugins', 'check_for_plugin_update');
add_filter('plugins_api', 'plugin_api_call', 10, 3);

function check_for_plugin_update($transient) {
    lc_log('Running check_for_plugin_update...');
    if (empty($transient->checked)) {
        lc_log('No checked plugins found.');
        return $transient;
    }

    $remote_version = get_remote_plugin_version();
    lc_log('Remote version: ' . $remote_version);

    if ($remote_version && version_compare(LC_VERSION, $remote_version, '<')) {
        $plugin_data = get_plugin_data(LC_FILE);
        $slug = plugin_basename(LC_FILE);

        $transient->response[$slug] = (object) [
            'slug' => $slug,
            'new_version' => $remote_version,
            'url' => 'https://github.com/kerackdiaz/LicenserCoreWP',
            'package' => 'https://github.com/kerackdiaz/LicenserCoreWP/archive/refs/tags/V' . $remote_version . '.zip',
        ];
        lc_log('Update available: ' . print_r($transient->response[$slug], true));
    } else {
        lc_log('No update available.');
    }

    return $transient;
}

function get_remote_plugin_version() {
    lc_log('Fetching remote plugin version...');
    $remote_info = wp_remote_get('https://raw.githubusercontent.com/kerackdiaz/LicenserCoreWP/main/README.md');
    if (is_wp_error($remote_info) || wp_remote_retrieve_response_code($remote_info) != 200) {
        lc_log('Error fetching remote info: ' . print_r($remote_info, true));
        return false;
    }

    $remote_info = wp_remote_retrieve_body($remote_info);
    if (preg_match('/^Stable tag:\s*(\d+\.\d+\.\d+)/m', $remote_info, $matches)) {
        lc_log('Remote version found: ' . $matches[1]);
        return $matches[1];
    }

    lc_log('No remote version found.');
    return false;
}

function plugin_api_call($default, $action, $args) {
    lc_log('Running plugin_api_call...');
    if ($action != 'plugin_information') {
        return false;
    }

    if ($args->slug != plugin_basename(LC_FILE)) {
        return false;
    }

    $remote_info = wp_remote_get('https://raw.githubusercontent.com/kerackdiaz/LicenserCoreWP/main/README.md');
    if (is_wp_error($remote_info) || wp_remote_retrieve_response_code($remote_info) != 200) {
        lc_log('Error fetching remote info: ' . print_r($remote_info, true));
        return false;
    }

    $remote_info = wp_remote_retrieve_body($remote_info);

    $plugin_info = [
        'name' => 'Licenser Core',
        'slug' => plugin_basename(LC_FILE),
        'version' => get_remote_plugin_version(),
        'author' => 'KerackDiaz',
        'homepage' => 'https://github.com/kerackdiaz/LicenserCoreWP',
        'sections' => [
            'description' => 'Este plugin es exclusivo para la administración y venta de licencias agencias que deseen usar licenser.',
        ],
        'download_link' => 'https://github.com/kerackdiaz/LicenserCoreWP/archive/refs/tags/V' . get_remote_plugin_version() . '.zip',
    ];

    lc_log('Plugin info: ' . print_r($plugin_info, true));
    return (object) $plugin_info;
}