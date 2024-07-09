<?php
namespace Jankx\WooCommerce\Integration\Elementor;

use Jankx\WooCommerce\Integration\Elementor\Widgets\CategoryTabsProducts;
use Jankx\WooCommerce\Integration\Elementor\Widgets\Products;
use Jankx\Elementor\Compatibles\ElementorCompatible;

class Elementor
{
    public function __construct()
    {
        add_action(
            'elementor/widgets/register',
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
        ElementorCompatible::registerWidget($widgets_manager, new CategoryTabsProducts());
        ElementorCompatible::registerWidget($widgets_manager, new Products());
    }

    public function registerWooCommerceFrontend()
    {
        if (function_exists('WC')) {
            WC()->frontend_includes();
        }
    }
}
