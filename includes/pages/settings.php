<div id="settings" class="tab-content" style="display: none;">
    <h2>Settings</h2>
    <form method="post" action="options.php">
        <?php
        settings_fields('licenser_core_settings');
        do_settings_sections('licenser_core_settings');
        ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Public Key</th>
                <td><input type="text" name="licenser_core_public_key" value="<?php echo esc_attr(get_option('licenser_core_public_key')); ?>" /></td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>