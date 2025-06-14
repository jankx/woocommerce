<?php

namespace Jankx\WooCommerce\Abstracts;

if (!defined('ABSPATH')) {
    exit('Cheating huh?');
}

use Jankx\GlobalConfigs;
use Jankx\PostLayout\Abstracts\LoopItemContent;

abstract class ProductLoopItemContent extends LoopItemContent
{
    public function getPostClassHook(): ?string
    {
        return 'woocommerce_post_class';
    }

    public function isShowDiscountPrice()
    {
        return apply_filters(
            'jankx/woocommerce/products/item/discount_price',
            GlobalConfigs::get('customs.woocommerce.discount_price', true)
        );
    }


    public function showPercentDiscount()
    {
    }
}
