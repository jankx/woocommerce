<?php

namespace Jankx\WooCommerce\Layouts\Loop;

use WC_Product;

use Jankx\PostLayout\Abstracts\LoopItemContent;
use Jankx\WooCommerce\WooCommerceTemplate;

class DetailAndBuyNowButton extends LoopItemContent
{
    protected $removeAddToCartLink = false;

    public static function getType()
    {
        return 'loop-dabnb';
    }

    public function contentStart()
    {
        remove_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10);
        remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5);

        add_action('woocommerce_after_shop_loop_item', [$this, 'addGoToDetailButton'], 8);

        add_action('woocommerce_after_shop_loop_item', [$this, 'openButtonsWrap'], 5);
        add_action('woocommerce_after_shop_loop_item', [$this, 'closeButtonsWrap'], 25);
    }

    public function contentEnd()
    {
        add_action('woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10);
        add_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5);

        remove_action('woocommerce_after_shop_loop_item', [$this, 'addGoToDetailButton'], 8);

        remove_action('woocommerce_after_shop_loop_item', [$this, 'openButtonsWrap'], 5);
        remove_action('woocommerce_after_shop_loop_item', [$this, 'closeButtonsWrap'], 25);
    }

    public function addGoToDetailButton()
    {
        WooCommerceTemplate::render(
            'woocommerce/loop/detail-button',
            [
            ]
        );
    }

    public function openButtonsWrap()
    {
        /**
         * \WC_Product
         */
        global $product;

        $buttonWrapClasses = ['jankx-ecommerce-buttons'];

        if (is_a($product, WC_Product::class)) {
            if (!($product->is_purchasable() && $product->is_in_stock())) {
                remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
                $this->removeAddToCartLink = true;
                $buttonWrapClasses[] = 'no-add-to-cart';
            }
        }

        printf('<div %s>', jankx_generate_html_attributes(
            apply_filters('jankx/woocommerce/loop/buttons/wrap/attributes', [
                'class' => $buttonWrapClasses,
            ])
        ));
    }

    public function closeButtonsWrap()
    {
        echo '</div>';
        if ($this->removeAddToCartLink) {
            add_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
        }
    }
}
