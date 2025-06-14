<?php

namespace Jankx\WooCommerce\Layouts\ProductDetail\NoSidebar;

if (!defined('ABSPATH')) {
    exit('Cheating huh?');
}

use Jankx\WooCommerce\Abstracts\ProductDetailContent;

class ImageAndProductInfosOnTopDescriptionBellow extends ProductDetailContent
{
    const NAME = 'top-image-n-info';

    public function __construct()
    {
        $this->appendBodyClass();

        add_action('woocommerce_before_single_product', [$this, 'initProductSummaryLayout']);
        // open top wrap
        add_action('woocommerce_before_single_product_summary', [$this, 'openTopProductInfoWrap'], 5);

        // start left block tags
        add_action('woocommerce_before_single_product_summary', [$this, 'startLeftBlockTopInfo'], 6);
        add_action('woocommerce_before_single_product_summary', [$this, 'endLeftBlockTopInfo'], 50);

        // start right block tags
        add_action('woocommerce_before_single_product_summary', [$this, 'startRightBlockTopInfo'], 55);
        add_action('woocommerce_after_single_product_summary', [$this, 'endRightBlockTopInfo'], 4);

        // close top wrap
        add_action('woocommerce_after_single_product_summary', [$this, 'closeTopProductInfoWrap'], 5);
    }


    public function getLayoutId()
    {
        return static::NAME;
    }
}
