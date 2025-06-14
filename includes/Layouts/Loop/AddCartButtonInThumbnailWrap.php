<?php

namespace Jankx\WooCommerce\Layouts\Loop;

if (!defined('ABSPATH')) {
    exit('Cheatin huh?');
}

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

    public function addCartButtonPosition($itemClasses)
    {
        $itemClasses[] = sprintf(
            'pos-%s',
            apply_filters('jankx/woocommerce/add_cart/button', 'bottom-right')
        );
        return $itemClasses;
    }

    public function contentStart()
    {
        remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
        add_action('jankx/post_layout/thumbnail/after', 'woocommerce_template_loop_add_to_cart', 10);

        add_filter($this->getPostClassHook(), [$this, 'addCartButtonPosition'], 10);


        add_action('woocommerce_loop_add_to_cart_link', [$this, 'renderCartButtonIcon'], 10, 2);
        add_action('woocommerce_before_shop_loop_item_title', [$this, 'wrapProductInfoTag'], 25);
        add_action('woocommerce_after_shop_loop_item', [$this, 'closeWrapProductInfoTag'], 45);


        remove_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10);
        remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5);

        add_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_link_open', 9);
        add_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_link_close', 11);
    }


    public function contentEnd()
    {

        add_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10);
        add_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5);

        remove_action('woocommerce_before_shop_loop_item_title', [$this, 'wrapProductInfoTag'], 25);
        remove_action('woocommerce_after_shop_loop_item', [$this, 'closeWrapProductInfoTag'], 45);
        remove_action('woocommerce_loop_add_to_cart_link', [$this, 'renderCartButtonIcon']);

        remove_filter($this->getPostClassHook(), [$this, 'addCartButtonPosition'], 10);

        remove_action('jankx/post_layout/thumbnail/after', 'woocommerce_template_loop_add_to_cart', 10);

        add_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
    }


    public function wrapProductInfoTag()
    {
        ?>
        <div class="product-info">
        <?php
    }
    public function closeWrapProductInfoTag()
    {
        ?>
        </div>
        <?php
    }
}

