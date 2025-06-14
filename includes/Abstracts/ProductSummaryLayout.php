<?php

namespace Jankx\WooCommerce\Abstracts;

if (!defined('ABSPATH')) {
    exit('Cheating huh?');
}

use Jankx\WooCommerce\Constracts\ProductSummaryLayoutInterface;

abstract class ProductSummaryLayout implements ProductSummaryLayoutInterface
{
    protected $isVariationChooser = false;

    protected $useSpinnerForQuantityInput = true;

    public function showCallNowButton()
    {
        return apply_filters(
            'jankx/woocommerce/product_detail/call_button/enabled',
            false,
            $this
        );
    }

    public function init()
    {
        // Init functions
    }

    public function isVariantionChooser()
    {
        return false;

        // return $this->isVariationChooser;
    }

    public function isUseSpinnerForQuantityInput()
    {
        return $this->useSpinnerForQuantityInput;
    }

    public function modifyVariationChooser($html, $args)
    {
    }

    public function modifyQuantityInput()
    {
    }
}
