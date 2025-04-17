<?php

namespace Jankx\WooCommerce\Layouts\ProductSummary;

use Jankx\WooCommerce\Abstracts\ProductSummaryLayout;

class ProductVariationChooserAndInputSpinner extends ProductSummaryLayout
{
    const NAME = 'variantion-chooser-and-input-spinner';

    protected $isVariationChooser = true;
    protected $useSpinnerForQuantityInput = true;

    protected $quantityInputArgs;

    /**
     * Summary of quanityInputStyle
     *
     * Allow only 3 values inputCenter2Conrol|spinnerRight|spinnerLeft
     *
     * @var string
     */
    protected $quanityInputStyle = 'inputCenter2Conrol';

    public function init()
    {
        add_filter('woocommerce_quantity_input_args', function ($args) {
            $this->quantityInputArgs = $args;
            return $args;
        }, 100);


        if ($this->isVariantionChooser()) {
            add_filter('script_loader_src', function ($script) {

                if (strpos($script, 'add-to-cart-variation') !== false) {
                    $addToCartVariationJs = jankx_woocommerce_asset_url('js/add-to-cart-variation.js');

                    $addToCartVariationJsPart2 = explode('add-to-cart-variation', $script);
                    $addToCartVariationJsPart1 = explode('add-to-cart-variation', $addToCartVariationJs);

                    $script = sprintf('%s%s%s', $addToCartVariationJsPart1[0], 'add-to-cart-variation', $addToCartVariationJsPart2[1]);
                    $this->jsURLModified = true;
                }

                return $script;
            });
        }

        add_action('woocommerce_after_add_to_cart_quantity', [$this, 'startButtonGroup']);
        add_action('woocommerce_after_add_to_cart_button', [$this, 'endButtonGroup']);
    }


    public function modifyVariationChooser($html, $args)
    {
        $options = $args['options'];
        $product = $args['product'];
        $attribute = $args['attribute'];
        $name = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title($attribute);
        $id = $args['id'] ? $args['id'] : sanitize_title($attribute);
        $class = $args['class'];
        $required = (bool) $args['required'];
        $show_option_none = (bool) $args['show_option_none'];
        $show_option_none_text = $args['show_option_none'] ? $args['show_option_none'] : __('Choose an option', 'woocommerce'); // We'll do our best to hide the placeholder, but we'll need to show something when resetting options.

        if (empty($options) && !empty($product) && !empty($attribute)) {
            $attributes = $product->get_variation_attributes();
            $options = $attributes[$attribute];
        }

        $html = '<div class="attribute-chooser">';

        if (!empty($options)) {
            if ($product && taxonomy_exists($attribute)) {
                // Get terms if this is a taxonomy - ordered. We need the names too.
                $terms = wc_get_product_terms(
                    $product->get_id(),
                    $attribute,
                    array(
                        'fields' => 'all',
                    )
                );

                foreach ($terms as $term) {
                    if (in_array($term->slug, $options, true)) {
                        $html .= '<label class="jank-variant-chooser-ctrl">';
                        $html .= '<input
                            type="radio"
                            id="' . esc_attr($id) . '"
                            class="' . esc_attr($class) . '"
                            name="' . esc_attr($name) . '"
                            value="' . esc_attr($term->slug) . '"
                            data-attribute_name="attribute_' . esc_attr(sanitize_title($attribute)) . '"
                            data-show_option_none="' . ( $show_option_none ? 'yes' : 'no' ) . '"' . selected(sanitize_title($args['selected']), $term->slug, false) . ( $required ? ' required' : '' ) . '
                        />' . esc_html(apply_filters('woocommerce_variation_option_name', $term->name, $term, $attribute, $product)) . '</label>';
                    }
                }
            } else {
                foreach ($options as $option) {
                    $html .= '<label class="jank-variant-chooser-ctrl">';
                    // This handles < 2.4.0 bw compatibility where text attributes were not sanitized.
                    $selected = sanitize_title($args['selected']) === $args['selected'] ? selected($args['selected'], sanitize_title($option), false) : selected($args['selected'], $option, false);
                    $html .= '<input
                        type="radio"
                        id="' . esc_attr($id) . '"
                        class="' . esc_attr($class) . '"
                        name="' . esc_attr($name) . '"
                        value="' . esc_attr($option) . '"
                        data-attribute_name="attribute_' . esc_attr(sanitize_title($attribute)) . '"
                        data-show_option_none="' . ( $show_option_none ? 'yes' : 'no' ) . '"' . $selected . ( $required ? ' required' : '' ) . '
                    />' . esc_html(apply_filters('woocommerce_variation_option_name', $option, null, $attribute, $product)) . '</label>';
                }
            }
        }

        $html .= '</div>';



        return $html;
    }

    public function modifyQuantityInput()
    {
        $allowedControlStyles = ['inputCenter2Conrol', 'spinnerRight', 'spinnerLeft'];

        $this->quanityInputStyle = apply_filters(
            'jankx/woocommerce/product/quantity/style',
            $this->quanityInputStyle,
            $this
        );

        if (!in_array($this->quanityInputStyle, $allowedControlStyles)) {
            return;
        }


        add_action('woocommerce_before_quantity_input_field', [$this, 'openQuanityInputWrapper'], 5);
        add_action('woocommerce_after_quantity_input_field', [$this, 'closeQuanityInputWrapper'], 55);

        if ($this->quanityInputStyle == 'inputCenter2Conrol') {
            add_action('woocommerce_before_quantity_input_field', [$this, 'decreaseQuantityControl']);
            add_action('woocommerce_after_quantity_input_field', [$this, 'increaseQuantityControl']);
        } elseif ($this->quanityInputStyle == 'spinnerRight') {
            add_action('woocommerce_before_quantity_input_field', [$this, 'decreaseQuantityControl']);
            add_action('woocommerce_before_quantity_input_field', [$this, 'increaseQuantityControl']);
        } else {
            add_action('woocommerce_after_quantity_input_field', [$this, 'decreaseQuantityControl']);
            add_action('woocommerce_after_quantity_input_field', [$this, 'increaseQuantityControl']);
        }
    }

    public function loadProductSummaryLayout()
    {
    }

    protected function startControlGroup()
    {
    }
    protected function endControlGroup()
    {
    }

    public function openQuanityInputWrapper()
    {
        printf('<div %s>', jankx_generate_html_attributes([
            'class' => ['jankx-summary-layout', $this->quanityInputStyle]
        ]));
        ob_start();
    }
    public function closeQuanityInputWrapper()
    {
        echo '</div><!-- closeQuanityInputWrapper -->';

        $this->changeQuanityInputToTextControl();
    }

    protected function changeQuanityInputToTextControl()
    {
        $html = ob_get_clean();

        $html = str_replace(
            'type="number"',
            'type="text"',
            $html
        );

        echo $html;
    }

    public function decreaseQuantityControl()
    {
        $this->startControlGroup();
        $inputId = array_get($this->quantityInputArgs, 'input_id');
        ?>
        <label for="<?php echo $inputId; ?>" class="quantity-control decrease">-</label>
        <?php
    }
    public function increaseQuantityControl()
    {
        $inputId = array_get($this->quantityInputArgs, 'input_id');
        ?>
        <label for="<?php echo $inputId; ?>" class="quantity-control increase">+</label>
        <?php
        $this->endControlGroup();
    }


    public function startButtonGroup()
    {
        echo sprintf('<div %s>', jankx_generate_html_attributes(
            apply_filters('jankx/woocommerce/add-cart/button-group/attrs', [
                'class' => ['button-group']
            ])
        ));
    }

    public function endButtonGroup()
    {
        do_action('jankx/woocommerce/product/buttons');
        echo '</div>';
    }
}
