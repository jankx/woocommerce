<?php
namespace Jankx\Ecommerce\Base\Layouts;

use Jankx\Ecommerce\EcommerceTemplate;
use Jankx\Ecommerce\Ecommerce;
use Jankx\Ecommerce\Base\Modules\ViewedProductsModule;
use Jankx\Ecommerce\Base\Modules\SimilarPriceProductsModule;

class ProductInfoTopWithSidebar
{
    const LAYOUT_NAME = 'product-info-top-with-summary-sidebar';

    public function __construct()
    {
        add_action('widgets_init', array($this, 'registerSidebars'));
        add_action('template_redirect', array($this, 'initFrontend'), 5);
        add_filter('woocommerce_output_related_products_args', array($this, 'changeRelatedProductColumns'));
    }

    public function initFrontend()
    {
        if (!is_singular('product')) {
            return;
        }
        add_filter('body_class', array($this, 'createBodyClass'));
        add_action('template_redirect', array($this, 'remove_woocommerce_template_single_contents'), 15);
        add_action('template_redirect', array($this, 'load_single_product_layout'), 20);

        add_action('jankx_template_after_header', function () {
            do_action('jankx_ecommerce_summary_content');
        }, 20);
    }

    public function createBodyClass($classes)
    {
        if (is_singular('product')) {
            array_push($classes, 'jxe-pitl');
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
            'jankx_ecommerce_woocommerce_product_summary_sidebar_args',
            $shopSidebarArgs
        ));
    }

    public function remove_woocommerce_template_single_contents()
    {
        remove_all_actions('woocommerce_before_single_product_summary');
        remove_all_actions('woocommerce_single_product_summary');
        remove_all_actions('woocommerce_after_single_product_summary');

        remove_action('woocommerce_before_single_product', 'woocommerce_output_all_notices', 10);
        add_action('jankx_ecommerce_product_info_top_after_product_name', 'woocommerce_output_all_notices');
    }

    public function print_product_name()
    {
        ?>
        <div class="product-name-on-top">
            <?php jankx_open_container(); ?>
                <?php woocommerce_template_single_title(); ?>

                <?php
                do_action(
                    'jankx_ecommerce_product_info_top_after_product_name'
                ); ?>
            <?php jankx_close_container(); ?>
        </div>
        <?php
    }

    public function print_the_summary_structure()
    {
        EcommerceTemplate::render('product-info-top/layout/summary');
    }

    public function print_product_stock()
    {
        EcommerceTemplate::render('product-info-top/product-stock');
    }

    public function print_notes()
    {
        EcommerceTemplate::render('product-info-top/notes');
    }

    public function print_call_to_order()
    {
        EcommerceTemplate::render('product-info-top/call-to-order');
    }

    public function print_related_products()
    {
        ?>
        <div id="related-products">
            <?php jankx_open_container(); ?>
                <?php woocommerce_output_related_products(); ?>
            <?php jankx_close_container(); ?>
        </div>
        <?php
    }

    public function changeRelatedProductColumns($args)
    {
        $args = array_merge($args, array(
            'posts_per_page' => 6,
            'columns'        => 6,
        ));
        return $args;
    }

    public function print_similar_price_products()
    {
        $similarProducts = new SimilarPriceProductsModule();
        echo $similarProducts;
    }

    public function print_viewed_products()
    {
        $viewedProducts = new ViewedProductsModule();
        echo $viewedProducts;
    }

    public function load_single_product_layout()
    {
        add_action('jankx_ecommerce_summary_content', array($this, 'print_product_name'), 5);
        add_action('jankx_ecommerce_summary_content', array($this, 'print_the_summary_structure'));
        add_action('jankx_ecommerce_summary_content', array($this, 'print_related_products'));

        add_action('jankx_ecommerce_product_info_top_left_block', 'woocommerce_show_product_images');

        add_action('jankx_ecommerce_product_info_top_right_block', 'woocommerce_template_single_price', 5);
        add_action('jankx_ecommerce_product_info_top_right_block', array($this, 'print_product_stock'), 15);
        add_action('jankx_ecommerce_product_info_top_right_block', 'woocommerce_template_single_add_to_cart', 20);
        add_action('jankx_ecommerce_product_info_top_right_block', array($this, 'print_notes'), 25);
        add_action('jankx_ecommerce_product_info_top_right_block', array($this, 'print_call_to_order'), 30);

        add_action('woocommerce_single_product_summary', 'woocommerce_product_description_tab');

        add_action('woocommerce_after_single_product', array($this, 'print_similar_price_products'));
        add_action('woocommerce_after_single_product', array($this, 'print_viewed_products'));
        add_action('woocommerce_after_single_product', 'woocommerce_upsell_display', 15);
        add_action('woocommerce_after_single_product', 'comments_template', 20);
    }
}
