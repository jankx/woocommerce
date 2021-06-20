<?php
namespace Jankx\Ecommerce\Integration\Elementor;

use Jankx\Ecommerce\Integration\Elementor\Widgets\CategoryTabsProducts;
use Jankx\Ecommerce\Integration\Elementor\Widgets\Products;
use Jankx\Ecommerce\Integration\Elementor\Widgets\ViewedProducts;

class Elementor
{
    public function __construct()
    {
        add_action(
            'elementor/widgets/widgets_registered',
            array($this, 'registerWidgets')
        );
        if (! empty($_REQUEST['action']) && 'elementor' === $_REQUEST['action'] && is_admin()) {
            add_action('init', [ $this, 'registerWooCommerceFrontend' ], 5);
        }
    }

    /**
     * Register Jankx eCommerce Widgets
     *
     * @param \Elementor\Widgets_Manager $widgets_manager
     * @return void
     */
    public function registerWidgets($widgets_manager)
    {
        $widgets_manager->register_widget_type(new CategoryTabsProducts());
        $widgets_manager->register_widget_type(new Products());
        $widgets_manager->register_widget_type(new ViewedProducts());
    }

    public function registerWooCommerceFrontend()
    {
        if (function_exists('WC')) {
            WC()->frontend_includes();
        }
    }
}
