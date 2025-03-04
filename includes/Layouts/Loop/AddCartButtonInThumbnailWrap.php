<?php

namespace Jankx\WooCommerce\Layouts\Loop;

use Jankx\WooCommerce\Abstracts\ProductLoopItemContent;

class AddCartButtonInThumbnailWrap extends ProductLoopItemContent
{
    const LOOP_LAYOUT_NAME = 'cart-btn-in-thumb';
    public static function getType()
    {
        return static::LOOP_LAYOUT_NAME;
    }

    public function renderCartButtonIcon($cartLink, $product)
    {
        return $cartLink;
    }

    public function contentStart()
    {
        remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
        add_action('jankx/post_layout/thumbnail/after', 'woocommerce_template_loop_add_to_cart', 10);
        add_filter('woocommerce_loop_add_to_cart_link', [$this, 'renderCartButtonIcon'], 10, 2);
    }


    public function contentEnd()
    {
        remove_filter('woocommerce_loop_add_to_cart_link', [$this, 'renderCartButtonIcon']);
        remove_action('jankx/post_layout/thumbnail/after', 'woocommerce_template_loop_add_to_cart', 10);
        add_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
    }
}
