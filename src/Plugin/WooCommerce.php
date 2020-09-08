<?php
namespace Jankx\Ecommerce\Plugin;

use Jankx\Ecommerce\Template;

class WooCommerce
{
    const PLUGIN_NAME = 'WooCommerce';

    public function __construct()
    {
        $this->initHooks();
    }

    public function initHooks()
    {
        add_action('widgets_init', array($this, 'registerShopSidebars'));

        add_action('jankx_page_template_single_product', array($this, 'renderProductContent'));
        add_action('jankx_template_build_site_layout', array($this, 'customShopLayout'));
    }

    public function registerShopSidebars()
    {
        $shopSidebarArgs = array(
            'id' => 'shop',
            'name' => __('Shop Sidebar', 'jankx'),
            'description' => __('The widgets of the shop will be show at here', 'jankx'),
            'before_widget' => '<section id="%1$s" class="widget jankx-widget %2$s">',
            'after_widget' => '</section>',
            'before_title' => '<h3 class="jankx-title widget-title">',
            'after_title' => '</h3>',
        );

        // Register shop sidebar
        register_sidebar(apply_filters(
            'jankx_ecommerce_woocommerce_sidebar_args',
            $shopSidebarArgs
        ));
    }

    public function customShopLayout($layoutLoader)
    {
        if (is_woocommerce()) {
            remove_action('jankx_template_after_main_content', 'get_sidebar', 35);
            remove_action('jankx_template_after_main_content', array($layoutLoader, 'loadSecondarySidebar'), 45);

            add_action('jankx_template_after_main_content', array($this, 'createWooCommerceSidebar'), 35);
            add_action('jankx_sidebar_shop_content', array($this, 'renderShopSidebar'));
        }
    }

    public function createWooCommerceSidebar() {
        do_action( 'woocommerce_sidebar' );
    }

    public function renderShopSidebar() {
        return Template::render('woocommerce/shop-sidebar');
    }

    public function renderProductContent()
    {
        return Template::render(
            'woocommerce/single-product'
        );
    }
}
