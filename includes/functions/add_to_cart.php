<?php
class LicenserCart {
    public function __construct() {
        add_action('wp_ajax_add_to_cart_custom', [$this, 'add_to_cart_custom']);
        add_action('wp_ajax_nopriv_add_to_cart_custom', [$this, 'add_to_cart_custom']);
        add_filter('woocommerce_before_calculate_totals', [$this, 'update_cart_item_data']);
    }

    public function add_to_cart_custom() {
        if (!isset($_POST['product_id'], $_POST['license_type'], $_POST['license_price'], $_POST['product_name'])) {
            error_log('Invalid data: ' . print_r($_POST, true));
            wp_send_json_error(['message' => 'Invalid data']);
        }

        $product_id = intval($_POST['product_id']);
        $license_type = sanitize_text_field($_POST['license_type']);
        $license_price = floatval($_POST['license_price']);
        $product_name = sanitize_text_field($_POST['product_name']);

        // Verificar si el producto con el mismo tipo de licencia ya está en el carrito
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            if ($cart_item['product_id'] == $product_id && $cart_item['license_type'] == $license_type) {
                wp_send_json_error(['message' => 'El producto ya está en el carrito.']);
                return;
            }
        }

        // Eliminar el producto anterior si es necesario
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            if ($cart_item['product_id'] == $product_id) {
                WC()->cart->remove_cart_item($cart_item_key);
            }
        }

        $cart_item_data = [
            'license_type' => $license_type,
            'license_price' => $license_price,
            'custom_name' => $product_name
        ];

        error_log('Adding to cart: ' . print_r($cart_item_data, true));

        $cart_item_key = WC()->cart->add_to_cart($product_id, 1, 0, [], $cart_item_data);

        if ($cart_item_key) {
            // Store the license type in a transient
            set_transient('licenser_license_type', $license_type, 5 * 60); // 5 min expiration
            wp_send_json_success(['cart_url' => wc_get_cart_url()]);
        } else {
            error_log('Failed to add product to cart.');
            wp_send_json_error(['message' => 'Failed to add product to cart.']);
        }
    }

    public function update_cart_item_data($cart) {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            if (isset($cart_item['license_price'])) {
                $cart_item['data']->set_price($cart_item['license_price']);
                $cart_item['data']->set_name($cart_item['custom_name']);
            }
        }
    }
}