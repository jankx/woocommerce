<?php
namespace Jankx\Ecommerce\Plugin;

class WooCommerce
{
    const PLUGIN_NAME = 'WooCommerce';

    public function __construct()
    {
        $this->initHooks();
    }

    public function initHooks()
    {
        add_action('init', array($this, 'init'));
        add_action('jankx_page_template_single_product', array($this, 'renderProductContent'));
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

    public function renderProductContent()
    {
    }
}
