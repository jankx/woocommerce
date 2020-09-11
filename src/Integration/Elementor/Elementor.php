<?php
namespace Jankx\Ecommerce\Integration\Elementor;

use Jankx\Ecommerce\Integration\Elementor\Widgets\CategoryTabsProducts;

class Elementor
{
    public function __construct()
    {
        add_action(
            'elementor/widgets/widgets_registered',
            array($this, 'registerWidgets')
        );
        add_action(
            'jankx_integrate_elementor_custom_widget_category',
            array($this, 'activeWooCommerceTab'),
            10,
            3
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
    }

    public function activeWooCommerceTab($widgetCategoryRefProp, $widgetCategory, $elementManager)
    {
        unset($widgetCategory['woocommerce-elements']['active']);
        $widgetCategoryRefProp->setValue($elementManager, $widgetCategory);
    }
}
