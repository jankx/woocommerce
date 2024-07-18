<?php

namespace Jankx\WooCommerce;

class MenuItems
{
    protected static $ecommerceMenuItems;

    public function register()
    {
        add_filter('jankx/layout/site/menu/itemtypes', array($this, 'registerWooCommerceItems'));
        add_filter('jankx_site_layout_nav_item_callback', array($this, 'customMenuItemCallable'), 10, 2);

        add_filter('jankx_site_layout_cart_icon_menu_item', array($this, 'cartIconItem'), 10, 2);
    }

    public static function getWooCommerceMenuItems()
    {
        if (!is_null(static::$ecommerceMenuItems)) {
            return static::$ecommerceMenuItems;
        }
        return static::$ecommerceMenuItems = array(
            'cart_icon' => __('Cart Icon', 'jankx'),
        );
    }

    public function registerWooCommerceItems($items)
    {
        $items = array_merge(
            $items,
            static::getWooCommerceMenuItems()
        );

        return $items;
    }

    public function customMenuItemCallable($callable, $item)
    {
        $ecommerceItems = static::getWooCommerceMenuItems();

        if (isset($ecommerceItems[$item->type])) {
            if ($item->type === 'cart_icon') {
                return array($this, 'cartIconItemContent');
            }
        }
        return $callable;
    }

    public function cartIconItem($item, $key)
    {
        $item['title'] = __("Cart Icon", 'jankx_woocommerce');
        return $item;
    }

    public function cartIconItemContent($item, $depth, $args)
    {
        return jankx_component('cart_button', array(
        ));
    }
}
