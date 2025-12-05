<?php

namespace Jankx\WooCommerce\Layouts\Checkout;

use Jankx\WooCommerce\Abstracts\Layouts\AbstractCheckoutPageLayout;

/**
 * Class DefaultCheckoutLayout
 * 
 * Default checkout page layout
 */
class DefaultCheckoutLayout extends AbstractCheckoutPageLayout
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(
            'default-checkout',
            __('Default Checkout', 'jankx-woocommerce'),
            'checkout-page'
        );

        $this->checkoutLayout = 'two-column';
        $this->isMultiStep = false;
        $this->priority = 10;

        $this->supportedSettings = [
            'layout_style',
            'field_spacing',
            'button_color',
            'show_order_notes',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function generateDynamicCss(array $settings): string
    {
        $css = parent::generateDynamicCss($settings);

        if (isset($settings['layout_style']) && $settings['layout_style'] === 'single-column') {
            $css .= '
                .checkout-page-default-checkout .checkout-form {
                    max-width: 800px;
                    margin: 0 auto;
                }
            ';
        }

        if (isset($settings['show_order_notes']) && !$settings['show_order_notes']) {
            $css .= '.order-notes { display: none; }';
        }

        return $css;
    }
}

