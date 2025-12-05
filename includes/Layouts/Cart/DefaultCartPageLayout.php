<?php

namespace Jankx\WooCommerce\Layouts\Cart;

use Jankx\WooCommerce\Abstracts\Layouts\AbstractCartPageLayout;

/**
 * Class DefaultCartPageLayout
 * 
 * Default cart page layout
 */
class DefaultCartPageLayout extends AbstractCartPageLayout
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(
            'default-cart-page',
            __('Default Cart Page', 'jankx-woocommerce'),
            'cart-page'
        );

        $this->pageLayout = 'fullwidth';
        $this->showCrossSells = true;
        $this->showShippingCalculator = true;
        $this->priority = 10;

        $this->supportedSettings = [
            'show_cross_sells',
            'show_shipping_calculator',
            'table_style',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function generateDynamicCss(array $settings): string
    {
        $css = parent::generateDynamicCss($settings);

        if (isset($settings['table_style']) && $settings['table_style'] === 'minimal') {
            $css .= '
                .cart-table {
                    border: none;
                }
                .cart-table th,
                .cart-table td {
                    border-bottom: 1px solid #eee;
                }
            ';
        }

        return $css;
    }
}

