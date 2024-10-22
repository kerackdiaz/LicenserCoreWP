<?php
if (!defined('ABSPATH')) {
    exit;
}

function licenser_core_clients_page() {
    $public_key = get_option('licenser_core_public_key');

    if (!$public_key) {
        echo '<div class="error"><p>Please set the API key in the settings first.</p></div>';
        return;
    }

    if (isset($_POST['change_status'])) {
        $company_id = sanitize_text_field($_POST['company_id']);
        $response = wp_remote_request("http://localhost:8080/api/master-admin/changeStatus/{$company_id}", [
            'method' => 'PUT',
            'headers' => [
                'Authorization' => 'Key ' . $public_key
            ]
        ]);

        if (is_wp_error($response)) {
            echo '<div class="error"><p>Failed to update company status.</p></div>';
        } else {
            echo '<div class="updated"><p>Company status updated successfully!</p></div>';
        }
    }

    $response = wp_remote_get('http://localhost:8080/api/master-admin/getallcompanies', [
        'headers' => [
            'Authorization' => 'Key ' . $public_key
        ]
    ]);

    if (is_wp_error($response)) {
        echo '<div class="error"><p>Failed to fetch companies.</p></div>';
        return;
    }

    $companies = json_decode(wp_remote_retrieve_body($response), true);

    ?>
    <div id="clients" class="tab-content" style="display: none;">
        <h2>Clients</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>License Type</th>
                    <th>Expiration Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (is_array($companies)) : ?>
                    <?php foreach ($companies as $company) : ?>
                        <tr>
                            <td><?php echo esc_html($company['name']); ?></td>
                            <td><?php echo esc_html($company['email']); ?></td>
                            <td><?php echo esc_html(isset($company['licenseType']) ? $company['licenseType'] : 'N/A'); ?></td>
                            <td><?php echo esc_html($company['expirationDate']); ?></td>
                            <td><?php echo esc_html(isset($company['status']) ? $company['status'] : 'N/A'); ?></td>
                            <td>
                                <form method="post" action="">
                                    <input type="hidden" name="company_id" value="<?php echo esc_attr($company['id']); ?>" />
                                    <input type="submit" name="change_status" value="Change Status" class="button button-primary" />
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6">No companies found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

