<?php

namespace Jankx\WooCommerce\Layouts\ProductDetail;

use Jankx\WooCommerce\WooCommerceTemplate;

class ProductInfoTopWithSidebarBellowName extends ProductInfoTopWithSidebar
{
    const LAYOUT_NAME = 'top-info-sidebar-bellow-name';

    public function createTopProductInfo()
    {
        WooCommerceTemplate::render(
            'top-product-info/top-info-sidebar-bellow-name'
        );
    }

    public function createBodyClass($classes)
    {
        if (is_singular('product')) {
            array_push($classes, 'jankx-ecom-prod-top-layout', 'sidebar-bellow-name');
        }
        return $classes;
    }

    public function load_single_product_layout()
    {
        parent::load_single_product_layout();

        add_action('jankx/woocommerce/single/product/layout/top_info/main', array($this, 'createBlockSidebarWrap'), 32);
        add_action('jankx/woocommerce/single/product/layout/top_info/before_sidebar', array($this, 'endTagProductInfoAboveSidebar'));
        add_action('jankx/woocommerce/single/product/layout/top_info/after_sidebar', array($this, 'endTagProductInfoSidebarWrap'));
    }

    public function createBlockSidebarWrap()
    {
        echo '<div class="product-info-sidebar-wrap">';
        echo '<div class="product-info-above-sidebar">';
    }


    public function endTagProductInfoAboveSidebar()
    {
        echo '</div> <!-- /.product-info-above-sidebar -->';
    }

    public function endTagProductInfoSidebarWrap()
    {
        echo '</div> <!-- /.product-info-sidebar-wrap -->';
    }
}
