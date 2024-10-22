<?php
/**
* Plugin name: Licenser Core
* Plugin URL: http://www.licenser.com
* Description: Este plugin es exclusivo para la administración y venta de licencias agencias que deseen usar licenser.
* Version: 1.0.1
* Author: KerackDiaz
* Author URI: https://3mas1r.com
* License: GPL2
* License URL: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: licenser-core
* woocommerce tested up to: 5.7.1
* @package licenser-core
*/

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

if (!defined('LC_VERSION')) {
    define('LC_VERSION', '1.0.1');
}

/** Define las constantes antes de usarlas */
define('LC_FILE', __FILE__);
define('LC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LC_PLUGIN_DIR', plugin_dir_path(__FILE__));

if (!function_exists('is_plugin_active')) {
    include_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// Include the menu file
include_once LC_PLUGIN_DIR . 'includes/menu.php';

// Include the admin page file
include_once LC_PLUGIN_DIR . 'admin/admin_page.php';

// Include the product creation file
include_once LC_PLUGIN_DIR . 'includes/register/product-creation.php';

// Include the add to cart functionality
include_once plugin_dir_path(__FILE__) . 'includes/functions/add_to_cart.php';

// Include the form page creation file
include_once plugin_dir_path(__FILE__) . 'includes/register/create_form_page.php';

// Include the redirect on purchase file
include_once plugin_dir_path(__FILE__) . 'includes/functions/redirect_on_purchase.php';

if (!class_exists('Licenser_core')) {
    /**
     * Main Class start here
     */
    final class Licenser_core {
        private static $instance = null;

        /**
         * Get plugin instance.
         * 
         * @return Licenser_core
         * @static
         */
        public static function get_instance() {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Constructor function check compatible plugin before activating it
         */
        private function __construct() {
            register_activation_hook(LC_FILE, array($this, 'lc_activate'));
            register_deactivation_hook(LC_FILE, array($this, 'lc_deactivate'));
            add_action('plugins_loaded', array($this, 'is_compatible')); // Moved to plugins_loaded
            add_action('init', array($this, 'lc_load_add_on'));
            add_action('admin_menu', 'licenser_core_menu'); // Add this line to register the menu
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles')); // Enqueue admin styles
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts')); // Enqueue admin scripts
            add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_styles')); // Enqueue frontend styles
            add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts')); // Enqueue frontend scripts
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'plugin_action_links')); // Add action links
            add_action('admin_init', array($this, 'register_settings')); // Register settings

            // Agregar filtros para verificar actualizaciones
            add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_update'));
            add_filter('plugins_api', array($this, 'plugin_api_call'), 10, 3);
        }

        /**
         * Método para crear enlaces de acción para el plugin.
         */
        public function plugin_action_links($links) {
            $settings_link = '<a href="admin.php?page=licenser-core">' . __('Settings', 'licenser-core') . '</a>';
            array_unshift($links, $settings_link);
            return $links;
        }

        /**
         * Revisa si woocommerce o elementor esta activo o instalado
         */
        public function is_compatible() {
            if (!is_plugin_active('woocommerce/woocommerce.php')) {
                add_action('admin_notices', function() {
                    $this->admin_notice_missing_main_plugin('WooCommerce');
                });
                deactivate_plugins(plugin_basename(__FILE__));
                return;
            }
            if (!is_plugin_active('elementor/elementor.php')) {
                add_action('admin_notices', function() {
                    $this->admin_notice_missing_main_plugin('Elementor');
                });
                deactivate_plugins(plugin_basename(__FILE__));
                return;
            }
        }

        /**
         * Incluir Licenser add-on en el fichero de registro
         */
        public function lc_load_add_on() {
            if (is_plugin_active('woocommerce/woocommerce.php') && is_plugin_active('elementor/elementor.php')) {
                include LC_PLUGIN_DIR . 'includes/register/class-licenser-elementor-page.php';
                if (class_exists('Lc_Register_Elementor_Page')) {
                    Lc_Register_Elementor_Page::get_instance();
                } else {
                    add_action('admin_notices', function() {
                        $this->admin_notice_missing_main_plugin('Lc_Register_Elementor_Page');
                    });
                    deactivate_plugins(plugin_basename(__FILE__));
                }
            }
        }

        /**
         * Muestra una notificacion si Elementor o Woocommerce no esta activo
         */
        public function admin_notice_missing_main_plugin($plugin_name) {
            $message = sprintf(
                esc_html__('%1$s requiere que %2$s este activo. Por favor activa %2$s para continuar.', 'licenser-core'),
                '<strong>' . esc_html__('Licenser Core', 'licenser-core') . '</strong>',
                '<strong>' . esc_html__($plugin_name, 'licenser-core') . '</strong>'
            );
            printf('<div class="notice notice-error is-dismissible"><p>%1$s</p></div>', $message);
            deactivate_plugins(plugin_basename(__FILE__));
        }

        /**
         * Agregar opciones a los detalles del plugin
         */
        public static function lc_activate() {
            update_option('lc_version', LC_VERSION);
            update_option('Lc_type', 'free');
            update_option('Lc_installDate', gmdate('Y-m-d h:i:s'));
            licenser_core_create_product();
        }

        /**
         * Función que se ejecuta al desactivar el plugin
         */
        public static function lc_deactivate() {
            flush_rewrite_rules();
        }

       /**
         * Configurar el updater
         */
        private function setup_updater() {
            $config = array(
                'slug' => plugin_basename(__FILE__),
                'proper_folder_name' => dirname(plugin_basename(__FILE__)),
                'api_url' => 'https://api.github.com/repos/tu-usuario/tu-repositorio',
                'raw_url' => 'https://raw.githubusercontent.com/tu-usuario/tu-repositorio/main',
                'github_url' => 'https://github.com/tu-usuario/tu-repositorio',
                'zip_url' => 'https://github.com/tu-usuario/tu-repositorio/archive/refs/heads/main.zip',
                'sslverify' => true,
                'access_token' => '', // Si tienes un token de acceso, agrégalo aquí
            );

            new \LicenserCore\Updater\Updater($config);
        }


        // Encolar el archivo CSS para el frontend
        public function enqueue_frontend_styles() {
            wp_enqueue_style('licenser-form-styles', plugin_dir_url(__FILE__) . 'assets/css/form-styles.css');
            wp_enqueue_style('sweetalert2', plugin_dir_url(__FILE__) . 'assets/css/sweetalert2.min.css');
        }

        // Encolar el archivo CSS para el backend
        public function enqueue_admin_styles() {
            wp_enqueue_style('licenser-admin-styles', plugin_dir_url(__FILE__) . 'assets/css/admin-style.css');
        }

        // Encolar los scripts para el backend
        public function enqueue_admin_scripts() {
            wp_enqueue_script('licenser-core-tabs', plugin_dir_url(__FILE__) . 'assets/js/tabs.js', array(), '1.0.0', true);
        }

        // Encolar los scripts para el frontend
        public function enqueue_frontend_scripts() {
            wp_enqueue_script('licenser-widget', plugin_dir_url(__FILE__) . 'assets/js/licenser-widget.js', ['jquery'], '1.0.0', true);
            wp_enqueue_script('sweetalert2', plugin_dir_url(__FILE__) . 'assets/js/sweetalert2.min.js', [], null, true);
            wp_localize_script('licenser-widget', 'ajaxurl', admin_url('admin-ajax.php'));
        }

        /**
         * Register settings
         */
        public function register_settings() {
            register_setting('licenser_core_settings', 'licenser_core_public_key');
            register_setting('licenser_core_licenses', 'licenser_core_license_demo');
            register_setting('licenser_core_licenses', 'licenser_core_license_mensual');
            register_setting('licenser_core_licenses', 'licenser_core_license_trimestral');
            register_setting('licenser_core_licenses', 'licenser_core_license_semestral');
            register_setting('licenser_core_licenses', 'licenser_core_license_anual');
            register_setting('licenser_core_licenses', 'licenser_core_license_vitalicia');
        }
    }

    Licenser_core::get_instance();
}

// Instanciar la clase LicenserCart
if (class_exists('LicenserCart')) {
    new LicenserCart();
}