<?php
if (!defined('ABSPATH')) {
    exit;
}

function licenser_core_licenses_page() {
    ?>
    <div class="wrap">
        <h1>Licenses</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('licenser_core_licenses');
            do_settings_sections('licenser_core_licenses');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">DEMO</th>
                    <td><input type="text" name="licenser_core_license_demo" value="<?php echo esc_attr(get_option('licenser_core_license_demo')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">MENSUAL</th>
                    <td><input type="text" name="licenser_core_license_mensual" value="<?php echo esc_attr(get_option('licenser_core_license_mensual')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">TRIMESTRAL</th>
                    <td><input type="text" name="licenser_core_license_trimestral" value="<?php echo esc_attr(get_option('licenser_core_license_trimestral')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">SEMESTRAL</th>
                    <td><input type="text" name="licenser_core_license_semestral" value="<?php echo esc_attr(get_option('licenser_core_license_semestral')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">ANUAL</th>
                    <td><input type="text" name="licenser_core_license_anual" value="<?php echo esc_attr(get_option('licenser_core_license_anual')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">VITALICIA</th>
                    <td><input type="text" name="licenser_core_license_vitalicia" value="<?php echo esc_attr(get_option('licenser_core_license_vitalicia')); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}