<?php
if (!defined('ABSPATH')) {
    die();
}

// Eliminar el producto creado por SKU
$product_sku = 'licenser'; // Reemplaza con el SKU real del producto
$product_id = wc_get_product_id_by_sku($product_sku);
if ($product_id) {
    wp_delete_post($product_id, true);
}

// Eliminar la página creada por nombre
$page_title = 'Licenser Form'; // Reemplaza con el título real de la página
$page = get_page_by_title($page_title);
if ($page) {
    wp_delete_post($page->ID, true);
}

// Eliminar opciones de la base de datos
delete_option('lc_version');
delete_option('Lc_type');
delete_option('Lc_installDate');
delete_option('licenser_core_public_key');
delete_option('licenser_core_license_demo');
delete_option('licenser_core_license_mensual');
delete_option('licenser_core_license_trimestral');
delete_option('licenser_core_license_semestral');
delete_option('licenser_core_license_anual');
delete_option('licenser_core_license_vitalicia');