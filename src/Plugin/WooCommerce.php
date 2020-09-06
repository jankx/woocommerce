<?php
namespace Jankx\Ecommerce\Plugin;

class WooCommerce
{
    public function __construct()
    {
        $this->initHooks();
    }

    public function initHooks()
    {
        add_action('init', array($this, 'init'));
    }

    public function init()
    {
        /**
         * Remove default woocommerce sidebar
         *
         * Jankx replace this feature by Jankx site layout
         */
        remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
    }
}
