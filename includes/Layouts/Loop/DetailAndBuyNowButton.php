<?php

namespace Jankx\WooCommerce\Layouts\Loop;

use Jankx\PostLayout\Abstracts\LoopItemContent;
use Jankx\WooCommerce\WooCommerceTemplate;

class DetailAndBuyNowButton extends LoopItemContent
{
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
        printf('<div %s>', jankx_generate_html_attributes(
            apply_filters('jankx/woocommerce/loop/buttons/wrap/attributes', [
                'class' => ['jankx-ecommerce-buttons']
            ])
        ));
    }

    public function closeButtonsWrap()
    {
        echo '</div>';
    }
}
