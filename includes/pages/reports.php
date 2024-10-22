<?php
if (!defined('ABSPATH')) {
    exit;
}

function licenser_core_reports_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Reportes', 'licenser-core'); ?></h1>
        <form method="post" action="">
            <label for="report_type"><?php esc_html_e('Tipo de Informe', 'licenser-core'); ?></label>
            <select name="report_type" id="report_type">
                <option value="sales"><?php esc_html_e('Ventas', 'licenser-core'); ?></option>
                <option value="customers"><?php esc_html_e('Clientes', 'licenser-core'); ?></option>
            </select>
            <br><br>
            <label for="start_date"><?php esc_html_e('Fecha de Inicio', 'licenser-core'); ?></label>
            <input type="date" name="start_date" id="start_date" required>
            <br><br>
            <label for="end_date"><?php esc_html_e('Fecha de Fin', 'licenser-core'); ?></label>
            <input type="date" name="end_date" id="end_date" required>
            <br><br>
            <input type="submit" name="generate_report" value="<?php esc_attr_e('Generar Informe', 'licenser-core'); ?>" class="button-primary">
        </form>
    </div>
    <?php

    if (isset($_POST['generate_report'])) {
        $report_type = sanitize_text_field($_POST['report_type']);
        $start_date = sanitize_text_field($_POST['start_date']);
        $end_date = sanitize_text_field($_POST['end_date']);

        // Generar el informe basado en el tipo y las fechas
        licenser_core_generate_report($report_type, $start_date, $end_date);
    }
}

function licenser_core_generate_report($report_type, $start_date, $end_date) {
    // Aquí puedes agregar la lógica para generar el informe
    // Por ejemplo, puedes generar un archivo CSV o PDF y ofrecerlo para descargar

    if ($report_type == 'sales') {
        // Generar informe de ventas
        $filename = 'sales_report_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename=' . $filename);

        $output = fopen('php://output', 'w');
        fputcsv($output, array('Fecha', 'Producto', 'Cantidad', 'Total'));

        // Obtener datos de ventas (esto es solo un ejemplo, ajusta según tus necesidades)
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT order_date, product_name, quantity, total
            FROM {$wpdb->prefix}sales
            WHERE order_date BETWEEN %s AND %s
        ", $start_date, $end_date));

        foreach ($results as $row) {
            fputcsv($output, (array) $row);
        }

        fclose($output);
        exit;
    } elseif ($report_type == 'customers') {
        // Generar informe de clientes
        $filename = 'customers_report_' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename=' . $filename);

        $output = fopen('php://output', 'w');
        fputcsv($output, array('Nombre', 'Email', 'Fecha de Registro'));

        // Obtener datos de clientes (esto es solo un ejemplo, ajusta según tus necesidades)
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT display_name, user_email, user_registered
            FROM {$wpdb->prefix}users
            WHERE user_registered BETWEEN %s AND %s
        ", $start_date, $end_date));

        foreach ($results as $row) {
            fputcsv($output, (array) $row);
        }

        fclose($output);
        exit;
    }
}