<?php
namespace Jankx\Ecommerce\Integration\Elementor;

use Jankx\Ecommerce\Integration\Elementor\Widgets\CategoryTabsProducts;
use Jankx\Ecommerce\Integration\Elementor\Widgets\Products;

class Elementor
{
    public function __construct()
    {
        add_action(
            'elementor/widgets/widgets_registered',
            array($this, 'registerWidgets')
        );
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
    }
}
