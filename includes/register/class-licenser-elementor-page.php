<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Lc_Register_Elementor_Page {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('elementor/widgets/widgets_registered', array($this, 'register_widgets'));
    }

    public function register_widgets() {
        require_once LC_PLUGIN_DIR . 'includes/widgets/class-licenser-widget.php';
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Licenser_Widget());
    }
}

Lc_Register_Elementor_Page::get_instance();