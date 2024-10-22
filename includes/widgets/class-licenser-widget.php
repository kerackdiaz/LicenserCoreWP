<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Licenser_Widget extends \Elementor\Widget_Base {
    use \Elementor\Includes\Widgets\Traits\Button_Trait;

    public function get_name() {
        return 'licenser_widget';
    }

    public function get_title() {
        return __('Licenser Widget', 'licenser-core');
    }

    public function get_icon() {
        return 'eicon-code';
    }

    public function get_categories() {
        return ['general'];
    }

    protected function _register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'licenser-core'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        function get_licenser_core_licenses() {
            $licenses = [
                'demo' => 'DEMO',
                'mensual' => 'MENSUAL',
                'trimestral' => 'TRIMESTRAL',
                'semestral' => 'SEMESTRAL',
                'anual' => 'ANUAL',
                'vitalicia' => 'VITALICIA',
            ];

            // Filtrar licencias vacías (excepto cero)
            return array_filter($licenses, function($key) {
                $value = get_option('licenser_core_license_' . $key);
                return $value !== false && $value !== '';
            }, ARRAY_FILTER_USE_KEY);
        }

        $licenses = get_licenser_core_licenses();

        $this->add_control(
            'license_type',
            [
                'label' => __('License Type', 'licenser-core'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $licenses,
                'default' => key($licenses),
            ]
        );

        $this->add_control(
            'text',
            [
                'label' => __('Button Text', 'licenser-core'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'dynamic' => [
                    'active' => true,
                ],
                'default' => __('Add to Cart', 'licenser-core'),
                'placeholder' => __('Add to Cart', 'licenser-core'),
            ]
        );

        $this->add_control(
            'size',
            [
                'label' => __('Size', 'licenser-core'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'sm',
                'options' => self::get_button_sizes(),
                'style_transfer' => true,
            ]
        );

        $this->add_control(
            'selected_icon',
            [
                'label' => __('Icon', 'licenser-core'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'fa4compatibility' => 'icon',
                'skin' => 'inline',
                'label_block' => false,
            ]
        );

        $this->add_control(
            'icon_align',
            [
                'label' => __('Icon Position', 'licenser-core'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'default' => is_rtl() ? 'row-reverse' : 'row',
                'options' => [
                    'left' => [
                        'title' => __('Left', 'licenser-core'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'right' => [
                        'title' => __('Right', 'licenser-core'),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'selectors_dictionary' => [
                    'left' => 'row',
                    'right' => 'row-reverse',
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-button-content-wrapper' => 'flex-direction: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'icon_indent',
            [
                'label' => __('Icon Spacing', 'licenser-core'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem', 'custom'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .elementor-button .elementor-button-content-wrapper' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        function get_woocommerce_products() {
            $products = wc_get_products(['limit' => -1]);
            $product_options = [];
            foreach ($products as $product) {
                $product_options[$product->get_id()] = $product->get_name();
            }
            return $product_options;
        }

        $this->add_control(
            'button_css_id',
            [
                'label' => __('Button ID', 'licenser-core'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'dynamic' => [
                    'active' => true,
                ],
                'default' => '',
                'title' => __('Add your custom id WITHOUT the Pound key. e.g: my-id', 'licenser-core'),
                'description' => sprintf(__('Add your custom id WITHOUT the Pound key. e.g: my-id', 'licenser-core')),
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'product_id',
            [
                'label' => __('Product', 'licenser-core'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => get_woocommerce_products(),
                'default' => '',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Style', 'licenser-core'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->register_button_style_controls([
            'section_condition' => [],
            'alignment_default' => 'center',
            'alignment_control_prefix_class' => 'licenser%s-align-',
            'content_alignment_default' => 'center',
        ]);

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $this->add_render_attribute('button', 'class', 'elementor-button');
        $this->add_render_attribute('button', 'role', 'button');
        if (!empty($settings['button_css_id'])) {
            $this->add_render_attribute('button', 'id', $settings['button_css_id']);
        }

        // Obtener la URL de agregar al carrito del producto seleccionado
        $product_id = $settings['product_id'];
        $product = wc_get_product($product_id);
        $add_to_cart_url = $product ? $product->add_to_cart_url() : '#';

        // Definir la variable $licenses
        $licenses = get_licenser_core_licenses();

        ?>
        <div class="elementor-button-wrapper">
            <a href="#" <?php echo $this->get_render_attribute_string('button'); ?> data-product-id="<?php echo esc_attr($product_id); ?>" data-license-type="<?php echo esc_attr($settings['license_type']); ?>" data-license-price="<?php echo esc_attr(get_option('licenser_core_license_' . $settings['license_type'])); ?>">
                <span class="elementor-button-content-wrapper">
                    <span class="elementor-button-text"><?php echo esc_html($settings['text']); ?></span>
                </span>
            </a>
        </div>
        <?php
    }

    protected function _content_template() {
        ?>
        <#
        if ( '' === settings.text && '' === settings.selected_icon.value ) {
            return;
        }

        view.addRenderAttribute( 'wrapper', 'class', 'elementor-button-wrapper' );

        view.addRenderAttribute( 'button', 'class', 'elementor-button' );

        if ( '' !== settings.button_css_id ) {
            view.addRenderAttribute( 'button', 'id', settings.button_css_id );
        }

        if ( '' !== settings.size ) {
            view.addRenderAttribute( 'button', 'class', 'elementor-size-' + settings.size );
        }

        if ( '' !== settings.hover_animation ) {
            view.addRenderAttribute( 'button', 'class', 'elementor-animation-' + settings.hover_animation );
        }

        view.addRenderAttribute( 'icon', 'class', 'elementor-button-icon' );
        view.addRenderAttribute( 'text', 'class', 'elementor-button-text' );
        view.addInlineEditingAttributes( 'text', 'none' );
        var iconHTML = elementor.helpers.renderIcon( view, settings.selected_icon, { 'aria-hidden': true }, 'i' , 'object' ),
        migrated = elementor.helpers.isIconMigrated( settings, 'selected_icon' );
        #>
        <div {{{ view.getRenderAttributeString( 'wrapper' ) }}}>
            <a {{{ view.getRenderAttributeString( 'button' ) }}} href="#" data-product-id="{{ settings.product_id }}" data-license-type="{{ settings.license_type }}" data-license-price="{{ settings.license_price }}">
                <span class="elementor-button-content-wrapper">
                    <# if ( settings.icon || settings.selected_icon ) { #>
                    <span {{{ view.getRenderAttributeString( 'icon' ) }}} class="elementor-button-icon">
                        <# if ( ( migrated || ! settings.icon ) && iconHTML.rendered ) { #>
                            {{{ iconHTML.value }}}
                        <# } else { #>
                            <i class="{{ settings.icon }}" aria-hidden="true"></i>
                        <# } #>
                    </span>
                    <# } #>
                    <# if ( settings.text ) { #>
                    <span {{{ view.getRenderAttributeString( 'text' ) }}}>{{{ settings.text }}}</span>
                    <# } #>
                </span>
            </a>
        </div>
        <?php
    }
}