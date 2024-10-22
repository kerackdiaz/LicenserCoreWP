document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.elementor-widget-licenser_widget .elementor-button').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            let productId = button.getAttribute('data-product-id');
            let licenseType = button.getAttribute('data-license-type');
            let licensePrice = button.getAttribute('data-license-price');

            if (!productId || !licenseType || !licensePrice) {
                console.error('Required data attributes not found.');
                return;
            }

            let data = {
                action: 'add_to_cart_custom',
                product_id: productId,
                license_type: licenseType,
                license_price: licensePrice,
                product_name: 'Licenser Suscripción ' + licenseType.charAt(0).toUpperCase() + licenseType.slice(1).toLowerCase()
            };

            console.log('Sending data:', data);

            jQuery.post(ajaxurl, data, function(response) {
                if (response.success) {
                    window.location.href = response.data.cart_url;
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.data.message
                    });
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX request failed:', textStatus, errorThrown);
            });
        });
    });
});