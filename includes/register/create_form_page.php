<?php
if (!defined('ABSPATH')) {
    exit;
}

function licenser_create_form_page() {
    $page_title = 'Licenser Form';
    $page_content = '[licenser_form_shortcode]';
    $page_template = ''; // Use default template

    $page_check = get_page_by_title($page_title);
    if (!isset($page_check->ID)) {
        $new_page_id = wp_insert_post([
            'post_title' => $page_title,
            'post_content' => $page_content,
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_author' => 1,
            'post_template' => $page_template,
        ]);
    }
}

add_action('init', 'licenser_create_form_page');

function licenser_form_shortcode() {
    $public_key = get_option('licenser_core_public_key');

    if (!$public_key) {
        echo '<div class="error"><p>Please set the API key in the settings first.</p></div>';
        return;
    }
    if (!isset($_GET['order_id']) || !($order = wc_get_order(intval($_GET['order_id'])))) {
        return '<p>Invalid order ID.</p>';
    }

    $user_email = $order->get_billing_email();
    $license_type = strtoupper(get_transient('licenser_license_type')); // Retrieve and convert to uppercase

    if (!$license_type) {
        return '<p>License type not found.</p>';
    }

    // Realizar la petición al endpoint de clientes
    $response = wp_remote_get('http://localhost:8080/api/master-admin/getallcompanies', [
        'headers' => [
            'Authorization' => 'Key ' . $public_key
        ]
    ]);

    if (is_wp_error($response)) {
        return '<p>Error al conectar con el servidor.</p>';
    }

    $body = wp_remote_retrieve_body($response);
    $companies = json_decode($body, true);

    $email_exists = false;
    $company_id = null;
    if (is_array($companies)) {
        foreach ($companies as $company) {
            if (isset($company['email']) && $company['email'] === $user_email) {
                $email_exists = true;
                $company_id = $company['id']; // Assuming the company ID is in the 'id' field
                break;
            }
        }
    }

    if ($email_exists) {
        // Realizar la petición al endpoint de renovación de licencia
        $renewal_data = [
            'licensestatus' => true,
            'licenseType' => $license_type,
            'statusCompany' => true
        ];

        $renewal_response = wp_remote_post("http://localhost:8080/api/master-admin/renewal-license/{$company_id}", [
            'headers' => [
                'Authorization' => 'Key ' . $public_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($renewal_data)
        ]);

        if (is_wp_error($renewal_response)) {
            return '<p>Error al renovar la licencia.</p>';
        }

        return '<p>Gracias por tu compra. Tu licencia se ha renovado a ' . esc_html($license_type) . '.</p>';
    }

    // Mostrar el formulario si el correo no coincide
    ob_start();
    ?>
    <div class="licenser-form-container">
        <h2><?php esc_html_e('Register Your Company', 'licenser-core'); ?></h2>
        <form action="" method="post">
            <label for="company_name">Company Name</label>
            <input type="text" name="company_name" required />

            <label for="company_email">Company Email</label>
            <input type="email" name="company_email" value="<?php echo esc_attr($user_email); ?>" required />

            <input type="hidden" name="license_type" value="<?php echo esc_attr($license_type); ?>" />

            <label for="admin_first_name">Admin First Name</label>
            <input type="text" name="admin_first_name" required />

            <label for="admin_last_name">Admin Last Name</label>
            <input type="text" name="admin_last_name" required />

            <label for="admin_user_name">Admin User Name</label>
            <input type="text" name="admin_user_name" required />

            <label for="admin_email">Admin Email</label>
            <input type="email" name="admin_email" value="<?php echo esc_attr($user_email); ?>" required />

            <label for="admin_password">Admin Password</label>
            <input type="password" name="admin_password" required />

            <input type="submit" value="Create Company" class="button-primary" />
        </form>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('licenser_form_shortcode', 'licenser_form_shortcode');

function handle_form_submission() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['company_email'])) {
        $public_key = get_option('licenser_core_public_key');

        $data = [
            'companyName' => sanitize_text_field($_POST['company_name']),
            'companyEmail' => sanitize_email($_POST['company_email']),
            'licenseType' => strtoupper(sanitize_text_field($_POST['license_type'])), // Convert to uppercase
            'adminFirstName' => sanitize_text_field($_POST['admin_first_name']),
            'adminLastName' => sanitize_text_field($_POST['admin_last_name']),
            'adminUserName' => sanitize_text_field($_POST['admin_user_name']),
            'adminEmail' => sanitize_email($_POST['admin_email']),
            'adminPassword' => sanitize_text_field($_POST['admin_password']),
        ];

        $response = wp_remote_post('http://localhost:8080/api/master-admin/createnewcompany', [
            'headers' => [
                'Authorization' => 'Key ' . $public_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($data)
        ]);

        if (!is_wp_error($response)) {
            wp_redirect(home_url());
            exit;
        } else {
            echo '<p>Error al conectar con el servidor.</p>';
        }
    }
}

add_action('template_redirect', 'handle_form_submission');