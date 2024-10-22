<?php
if (!defined('ABSPATH')) {
    exit;
}

function licenser_redirect_on_purchase($order_id) {
    $order = wc_get_order($order_id);
    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        if ($product && $product->get_sku() === 'licenser') {
            $form_page = get_page_by_title('Licenser Form');
            if ($form_page) {
                $form_page_url = add_query_arg('order_id', $order_id, get_permalink($form_page->ID));
                wp_redirect($form_page_url);
                exit;
            }
        }
    }
}

add_action('woocommerce_thankyou', 'licenser_redirect_on_purchase');