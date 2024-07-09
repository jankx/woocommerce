<?php
namespace Jankx\WooCommerce\Layouts;

use Jankx\WooCommerce\WooCommerceTemplate;
use Jankx\WooCommerce\WooCommerce;
use Jankx\WooCommerce\Renderer\ViewedProductsRenderer;
use Jankx\WooCommerce\Renderer\SimilarPriceProductsRenderer;
use Jankx\WooCommerce\Widget\ProductAttributes;

class ProductInfoTopWithSidebar
{
    const LAYOUT_NAME = 'top-product-info-with-summary-sidebar';
    protected $topLayoutSidebarActive = true;

    public function __construct()
    {
        add_action('widgets_init', array($this, 'registerSidebars'));
        add_action('wp', array($this, 'init_frontend'));
    }

    public function init_frontend()
    {
        if (!is_singular('product')) {
            return;
        }
        $this->topLayoutSidebarActive = apply_filters(
            'jankx/woocommerce/single/product/layout/top_info/sidebar/enable',
            $this->topLayoutSidebarActive
        );

        add_filter('body_class', array($this, 'createBodyClass'));

        // Render to frontend
        add_action('jankx/template/header/after', array($this, 'createTopProductInfo'), 20);

        add_action('template_redirect', array($this, 'remove_woocommerce_template_single_contents'), 15);
        add_action('template_redirect', array($this, 'load_single_product_layout'), 20);

        remove_action('woocommerce_before_single_product', 'woocommerce_output_all_notices', 10);
        add_action('jankx/woocommerce/single/product/layout/top_info/before', 'woocommerce_output_all_notices');

        add_filter('woocommerce_output_related_products_args', array($this, 'change_related_product_columns'));

        // Layout is loaded
        do_action('jankx/woocommerce/single/product/layout/top_info/loaded', $this);
    }

    public function createTopProductInfo()
    {
        WooCommerceTemplate::render('top-product-info/top-info', array(
            'has_sidebar' => $this->topLayoutSidebarActive,
        ));
    }

    public function createBodyClass($classes)
    {
        if (is_singular('product')) {
            array_push($classes, 'jankx-ecom-prod-top-layout');
        }
        return $classes;
    }

    public function registerSidebars()
    {
        $shopSidebarArgs = array(
            'id' => 'product_info_sidebar',
            'name' => __('Product Info Sidebar', 'jankx'),
            'description' => __('The widgets of the product summary will be show at here', 'jankx'),
            'before_widget' => '<section id="%1$s" class="widget jankx-widget %2$s">',
            'after_widget' => '</section>',
            'before_title' => '<h3 class="jankx-title widget-title">',
            'after_title' => '</h3>',
        );

        // Register shop sidebar
        register_sidebar(apply_filters(
            'jankx_woocommerce_woocommerce_product_summary_sidebar_args',
            $shopSidebarArgs
        ));

        register_widget(ProductAttributes::class);
    }

    public function remove_woocommerce_template_single_contents()
    {
        remove_all_actions('woocommerce_before_single_product_summary');
        remove_all_actions('woocommerce_single_product_summary');
        remove_all_actions('woocommerce_after_single_product_summary');
    }

    public function change_related_product_columns($args)
    {
        $args = array_merge($args, array(
            'posts_per_page' => 6,
            'columns'        => 6,
        ));
        return $args;
    }

    public function load_single_product_layout()
    {
        add_filter('woocommerce_product_tabs', array($this, 'removeTabsProductInfo'));
        add_action('woocommerce_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
        add_action('woocommerce_after_single_product', 'woocommerce_template_single_sharing', 20);

        add_action('jankx/woocommerce/single/product/layout/top_info/image', 'woocommerce_show_product_sale_flash', 10);
        add_action('jankx/woocommerce/single/product/layout/top_info/image', 'woocommerce_show_product_images', 5);

        add_action('jankx/woocommerce/single/product/layout/top_info/main', 'woocommerce_template_single_title', 10);
        add_action('jankx/woocommerce/single/product/layout/top_info/main', 'woocommerce_template_single_meta', 20);
        add_action('jankx/woocommerce/single/product/layout/top_info/main', 'woocommerce_template_single_rating', 30);
        add_action('jankx/woocommerce/single/product/layout/top_info/main', 'woocommerce_template_single_price', 40);
        add_action('jankx/woocommerce/single/product/layout/top_info/main', 'woocommerce_template_single_add_to_cart', 50);



        add_action('jankx/template/main_content_sidebar/end', 'woocommerce_upsell_display', 15);
        add_action('jankx/template/main_content_sidebar/end', 'woocommerce_output_related_products', 20);
    }

    public function removeTabsProductInfo($tabs)
    {
        unset($tabs['additional_information']);

        return $tabs;
    }
}
