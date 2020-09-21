<?php
namespace Jankx\Ecommerce\Base;

class MenuItems
{
    protected static $ecommerceMenuItems;

    public function register()
    {
        add_filter('jankx_site_layout_menu_items', array($this, 'registerEcommerceItems'));
        add_filter('jankx_site_layout_nav_item_callback', array($this, 'customMenuItemCallable'), 10, 2);

        add_filter('jankx_site_layout_cart_icon_menu_item', array($this, 'cartIconItem'), 10, 2);
    }

    public static function getEcommerceMenuItems()
    {
        if (!is_null(static::$ecommerceMenuItems)) {
            return static::$ecommerceMenuItems;
        }
        return static::$ecommerceMenuItems = array(
            'cart_icon' => __('Cart Icon', 'jankx'),
        );
    }

    public function registerEcommerceItems($items)
    {
        $items = array_merge(
            $items,
            static::getEcommerceMenuItems()
        );

        return $items;
    }

    public function customMenuItemCallable($callable, $item)
    {
        $ecommerceItems = static::getEcommerceMenuItems();

        if (isset($ecommerceItems[$item->type])) {
            if ($item->type === 'cart_icon') {
                return array($this, 'cartIconItemContent');
            }
        }
        return $callable;
    }

    public function cartIconItem($item, $key)
    {
        $item['title'] = __("Cart Icon", 'jankx');
        return $item;
    }

    public function cartIconItemContent($item, $depth, $args)
    {
        return jankx_component('cart_button', array(
        ));
    }
}
