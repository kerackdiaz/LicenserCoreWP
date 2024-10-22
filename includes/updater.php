<?php
namespace LicenserCore\Updater;

if (!defined('ABSPATH')) {
    exit;
}

class Updater {

    private $config;
    private $github_data;

    public function __construct($config = array()) {
        $defaults = array(
            'slug' => plugin_basename(__FILE__),
            'plugin_basename' => plugin_basename(__FILE__),
            'proper_folder_name' => dirname(plugin_basename(__FILE__)),
            'sslverify' => true,
            'access_token' => '',
        );

        $this->config = wp_parse_args($config, $defaults);

        $this->set_defaults();

        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_update'), 100);
        add_filter('plugins_api', array($this, 'get_plugin_info'), 10, 3);
        add_filter('upgrader_post_install', array($this, 'upgrader_post_install'), 10, 3);
        add_filter('http_request_timeout', array($this, 'http_request_timeout'));
        add_filter('http_request_args', array($this, 'http_request_sslverify'), 10, 2);
    }

    public function set_defaults() {
        if (!empty($this->config['access_token'])) {
            extract(parse_url($this->config['zip_url'])); // $scheme, $host, $path
            $zip_url = $scheme . '://api.github.com/repos' . $path;
            $zip_url = add_query_arg(array('access_token' => $this->config['access_token']), $zip_url);
            $this->config['zip_url'] = $zip_url;
        }

        if (!isset($this->config['new_version'])) {
            $this->config['new_version'] = $this->get_new_version();
        }

        if (!isset($this->config['last_updated'])) {
            $this->config['last_updated'] = $this->get_date();
        }

        if (!isset($this->config['description'])) {
            $this->config['description'] = $this->get_description();
        }

        $plugin_data = $this->get_plugin_data();
        if (!isset($this->config['plugin_name'])) {
            $this->config['plugin_name'] = $plugin_data['Name'];
        }

        if (!isset($this->config['version'])) {
            $this->config['version'] = $plugin_data['Version'];
        }

        if (!isset($this->config['author'])) {
            $this->config['author'] = $plugin_data['Author'];
        }

        if (!isset($this->config['homepage'])) {
            $this->config['homepage'] = $plugin_data['PluginURI'];
        }

        if (!isset($this->config['readme'])) {
            $this->config['readme'] = 'README.md';
        }
    }

    public function http_request_timeout() {
        return 2;
    }

    public function http_request_sslverify($args, $url) {
        if ($this->get_zip_url() == $url) {
            $args['sslverify'] = $this->config['sslverify'];
        }
        return $args;
    }

    private function get_zip_url() {
        return str_replace('{release_version}', $this->config['new_version'], $this->config['zip_url']);
    }

    public function get_new_version() {
        $version = get_site_transient(md5($this->config['slug']) . '_new_version');

        if ($this->overrule_transients() || (!isset($version) || !$version || '' == $version)) {
            $raw_response = $this->remote_get(trailingslashit($this->config['raw_url']) . basename($this->config['slug']));

            if (is_wp_error($raw_response)) {
                $version = false;
            }

            if (is_array($raw_response)) {
                if (!empty($raw_response['body'])) {
                    preg_match('/.*Version\:\s*(.*)$/mi', $raw_response['body'], $matches);
                }
            }

            if (empty($matches[1])) {
                $version = false;
            } else {
                $version = $matches[1];
            }

            if (false === $version) {
                $raw_response = $this->remote_get(trailingslashit($this->config['raw_url']) . $this->config['readme']);

                if (is_wp_error($raw_response)) {
                    return $version;
                }

                preg_match('#^\s*`*~Current Version\:\s*([^~]*)~#im', $raw_response['body'], $__version);

                if (isset($__version[1])) {
                    $version_readme = $__version[1];
                    if (-1 == version_compare($version, $version_readme)) {
                        $version = $version_readme;
                    }
                }
            }

            if (false !== $version) {
                set_site_transient(md5($this->config['slug']) . '_new_version', $version, 60 * 60 * 6);
            }
        }

        return $version;
    }

    public function remote_get($query) {
        if (!empty($this->config['access_token'])) {
            $query = add_query_arg(array('access_token' => $this->config['access_token']), $query);
        }

        $raw_response = wp_remote_get($query, array(
            'sslverify' => $this->config['sslverify']
        ));

        return $raw_response;
    }

    public function get_github_data() {
        if (isset($this->github_data) && !empty($this->github_data)) {
            $github_data = $this->github_data;
        } else {
            $github_data = get_site_transient(md5($this->config['slug']) . '_github_data');

            if ($this->overrule_transients() || (!isset($github_data) || !$github_data || '' == $github_data)) {
                $github_data = $this->remote_get($this->config['api_url']);

                if (is_wp_error($github_data)) {
                    return false;
                }

                $github_data = json_decode($github_data['body']);

                set_site_transient(md5($this->config['slug']) . '_github_data', $github_data, 60 * 60 * 6);
            }

            $this->github_data = $github_data;
        }

        return $github_data;
    }

    public function get_date() {
        $_date = $this->get_github_data();
        return (!empty($_date->updated_at)) ? date('Y-m-d', strtotime($_date->updated_at)) : false;
    }

    public function get_description() {
        $_description = $this->get_github_data();
        return (!empty($_description->description)) ? $_description->description : false;
    }

    public function get_plugin_data() {
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $plugin_file = rtrim(WP_PLUGIN_DIR, '/') . '/' . $this->config['proper_folder_name'] . '/' . $this->config['slug'];
        if (!is_file($plugin_file)) {
            return false;
        }
        $data = get_plugin_data($plugin_file);
        return $data;
    }

    public function check_update($transient) {
        global $pagenow;

        if (!is_object($transient)) {
            $transient = new \stdClass();
        }

        if ('plugins.php' === $pagenow && is_multisite()) {
            return $transient;
        }

        $update = version_compare($this->config['new_version'], $this->config['version']);

        if (1 === $update) {
            if (!empty($transient->checked)) {
                $transient->last_checked = current_time('timestamp');
                $transient->checked[$this->config['plugin_basename']] = $this->config['new_version'];
            }

            $response = new \stdClass();
            $response->new_version = $this->config['new_version'];
            $response->plugin = $this->config['plugin_basename'];
            $response->slug = $this->config['proper_folder_name'];
            $response->url = add_query_arg(array('access_token' => $this->config['access_token']), $this->config['github_url']);
            $response->package = $this->get_zip_url();

            if (false !== $response) {
                $transient->response[$this->config['plugin_basename']] = $response;
            }
        }

        return $transient;
    }

    public function get_plugin_info($data, $action, $args) {
        if (!isset($args->slug) || $args->slug != $this->config['slug']) {
            return $data;
        }

        $data->slug = $this->config['slug'];
        $data->plugin_name = $this->config['plugin_name'];
        $data->version = $this->config['new_version'];
        $data->author = $this->config['author'];
        $data->homepage = $this->config['homepage'];
        $data->requires = $this->config['requires'];
        $data->tested = $this->config['tested'];
        $data->downloaded = 0;
        $data->last_updated = $this->config['last_updated'];
        $data->sections = array('description' => $this->config['description']);
        $data->download_link = $this->get_zip_url();

        return $data;
    }

    public function upgrader_post_install($true, $hook_extra, $result) {
        global $wp_filesystem;

        $proper_destination = WP_PLUGIN_DIR . '/' . $this->config['proper_folder_name'];
        $wp_filesystem->move($result['destination'], $proper_destination);
        $result['destination'] = $proper_destination;
        $activate = activate_plugin(WP_PLUGIN_DIR . '/' . $this->config['slug']);

        $fail = __('The plugin has been updated, but could not be reactivated. Please reactivate it manually.', 'licenser-core');
        $success = __('Plugin reactivated successfully.', 'licenser-core');
        echo is_wp_error($activate) ? $fail : $success;
        return $result;
    }

    public function overrule_transients() {
        global $pagenow;
        if ('update-core.php' === $pagenow && isset($_GET['force-check'])) {
            return true;
        }
        return (defined('WP_GITHUB_FORCE_UPDATE') && WP_GITHUB_FORCE_UPDATE);
    }
}