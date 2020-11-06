<?php
namespace Jankx\Ecommerce\Base\Layouts;

use Jankx\Ecommerce\EcommerceTemplate;
use Jankx\Ecommerce\Ecommerce;

class ProductInfoTopWithSummarySidebar
{
    const LAYOUT_NAME = 'product-info-top-with-summary-sidebar';

    public function __construct()
    {
        add_action('widgets_init', array($this, 'registerSidebars'));
    }

    public function registerSidebars() {
        $shopSidebarArgs = array(
            'id' => 'product_summary',
            'name' => __('Product Summary Sidebar', 'jankx'),
            'description' => __('The widgets of the product summary will be show at here', 'jankx'),
            'before_widget' => '<section id="%1$s" class="widget jankx-widget %2$s">',
            'after_widget' => '</section>',
            'before_title' => '<h3 class="jankx-title widget-title">',
            'after_title' => '</h3>',
        );

        // Register shop sidebar
        register_sidebar(apply_filters(
            'jankx_ecommerce_woocommerce_product_summary_sidebar_args',
            $shopSidebarArgs
        ));
    }
}
