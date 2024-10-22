<?php
if (!defined('ABSPATH')) {
    exit;
}

function licenser_core_create_product() {
    // Verificar si WooCommerce está activo
    if (!class_exists('WC_Product')) {
        return;
    }

    // Verificar si el producto ya existe
    $product_id = wc_get_product_id_by_sku('licenser');
    if ($product_id) {
        return;
    }

    // Crear el producto
    $product = new WC_Product();
    $product->set_name('Licenser');
    $product->set_status('publish');
    $product->set_catalog_visibility('visible');
    $product->set_price(1);
    $product->set_regular_price(1);
    $product->set_sku('licenser');
    $product->set_virtual(true);
    $product->set_sold_individually(true);
    $product->save();
}