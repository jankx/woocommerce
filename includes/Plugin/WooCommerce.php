<?php
namespace Jankx\Ecommerce\Plugin;

use Jankx\SiteLayout\SiteLayout;
use Jankx\Ecommerce\Abstracts\ShopPlugin;
use Jankx\Ecommerce\EcommerceTemplate;
use Jankx\Ecommerce\Traits\WooCommerceData;
use Jankx\PostLayout\Layout\Carousel;

class WooCommerce extends ShopPlugin
{
    use WooCommerceData;

    const PLUGIN_NAME = 'woocommerce';

    protected static $disableShopSidebar;

    protected $shopSidebarHook;

    public function __construct()
    {
        $this->initHooks();
    }

    public function getName()
    {
        return static::PLUGIN_NAME;
    }

    public function getCartUrl()
    {
    }

    public function getPostType()
    {
        return 'product';
    }

    public function getProductCategoryTaxonomy()
    {
        return 'product_cat';
    }

    public function initHooks()
    {
        // Make theme support WooCommerce
        add_theme_support('woocommerce');

        // Register WooCommerce widgets
        add_action('widgets_init', array($this, 'registerShopSidebars'));

        add_action('wp', array($this, 'init'));

        add_action("jankx/ecommerce/loop/before", array($this, 'customizeProductColumns'));

        add_action('jankx/layout/product/loop/start', function ($layout_name, $postLayoutInstance) {
            if (in_array($layout_name, array(Carousel::LAYOUT_NAME))) {
                return;
            }

            wc_set_loop_prop('columns', $postLayoutInstance->getOption('columns'));
            woocommerce_product_loop_start();
        }, 10, 2);

        add_action('jankx/layout/product/loop/end', function ($layout) {
            if (in_array($layout, array(Carousel::LAYOUT_NAME))) {
                return;
            }
            woocommerce_product_loop_end();
        });

        add_action('jankx/layout/product/loop/init', array($this, 'setContentWrapperTagForPostLayout'), 10, 2);
    }

    public function init()
    {
        add_action('jankx_template_build_site_layout', array($this, 'customShopLayout'));
        add_action('jankx_template_page_single_product', array($this, 'renderProductContent'));
        add_action('jankx_template_default_site_layout', array($this, 'changeDefaultSiteLayout'));

        // Custom WooCommercce templates
        add_filter('wc_get_template', array($this, 'changeWooCommerceTemplates'), 10, 5);

        // Make WooCommerce is global
        add_filter('body_class', array($this, 'addWoocommerceCSSBodyClass'));

        add_filter('template_include', array($this, 'loadCustomWooCommerceTemplates'), 15);
        add_action('template_redirect', array($this, 'customWooCommerceElements'));
        add_action('woocommerce_enqueue_styles', array($this, 'cleanWooCommerceStyleSheets'));
        add_filter('jankx_ecommerce_localize_object_data', array($this, 'registerGlobalVars'));

        add_action('jankx/template/renderer/pre', array($this, 'customizeArchiveProductPage'), 10, 5);
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

    protected function checkSidebarIsActive()
    {
        if (is_null(static::$disableShopSidebar)) {
            $siteLayout = SiteLayout::getInstance();
            static::$disableShopSidebar = apply_filters(
                'jankx_ecommerce_disable_shop_sidebar',
                $siteLayout->getLayout() === SiteLayout::LAYOUT_FULL_WIDTH
            );
        }

        return ! static::$disableShopSidebar;
    }

    public function customShopLayout($layoutLoader)
    {
        if (is_woocommerce()) {
            remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
            remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

            remove_action('jankx_template_after_main_content', 'get_sidebar', 35);
            remove_action('jankx_template_after_main_content', array($layoutLoader, 'loadSecondarySidebar'), 45);

            if ($this->checkSidebarIsActive()) {
                $this->shopSidebarHook = apply_filters(
                    'jankx/ecommerce/woocommerce/sidebar/hook_loader',
                    'jankx_template_after_main_content',
                    $layoutLoader
                );
                add_action($this->shopSidebarHook, array($this, 'createWooCommerceSidebar'), 35);
                add_action('jankx_sidebar_shop_content', array($this, 'renderShopSidebar'));
            }
        }
    }

    public function createWooCommerceSidebar()
    {
        do_action('woocommerce_sidebar');
    }

    public function changeDefaultSiteLayout($layout)
    {
        if (is_woocommerce()) {
            if (!is_single()) {
                return SiteLayout::LAYOUT_FULL_WIDTH;
            }
            $sidebarPosition = apply_filters('jankx_template_site_layout_shop_sidebar_position', 'right');
            if ($sidebarPosition === 'right') {
                return SiteLayout::LAYOUT_CONTENT_SIDEBAR;
            } else {
                return SiteLayout::LAYOUT_SIDEBAR_CONTENT;
            }
        }
        return $layout;
    }

    public function renderShopSidebar()
    {
        if ($this->shopSidebarHook) {
            return EcommerceTemplate::render('woocommerce/shop-sidebar');
        }
    }

    public function renderProductContent()
    {
        return EcommerceTemplate::render(
            $this->getName() . '/single-product'
        );
    }

    public function changeWooCommerceTemplates($template, $template_name, $args, $template_path, $default_path)
    {
        $jankxTemplate    = sprintf('woocommerce/%s', rtrim($template_name, '.php'));
        $searchedTemplate = EcommerceTemplate::search($jankxTemplate);

        // Return Jankx Ecommerce template when the template is existing
        if ($searchedTemplate) {
            return $searchedTemplate;
        }

        // Return default WooCommerce template when Jankx Ecommerce template is not found`
        return $template;
    }

    // Make WooCommerce body class is global
    public function addWoocommerceCSSBodyClass($classes)
    {
        if (!in_array('woocommerce', $classes)) {
            $classes[] = 'woocommerce';
        }
        return $classes;
    }

    public function loadCustomWooCommerceTemplates($template)
    {
        if (strpos($template, sprintf(WP_CONTENT_DIR . '/plugins/woocommerce')) !== false) {
            $t = null;
            if (is_singular('product')) {
                $t = 'woocommerce/single-product.php';
            } elseif (is_product_taxonomy()) {
                $t = 'woocommerce/archive-product.php';
            }

            if (!is_null($t)) {
                $searchedTemplate = locate_template(array(
                    sprintf('templates/ecommerce/%s', $t),
                    $t
                ), false);

                if (!$searchedTemplate) {
                    return sprintf(
                        '%s/customize/%s',
                        constant('JANKX_ECOMMERCE_ROOT_DIR'),
                        $t
                    );
                }
                return $searchedTemplate;
            }
        }
        return $template;
    }

    public function customWooCommerceElements()
    {
        if (is_woocommerce()) {
            // Remove woocommerce content wrapper
            remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper');
            remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end');

            add_action('jankx_template_after_header', array($this, 'before_main_content_sidebar'), 16);
            add_action('jankx_template_after_main_content_sidebar', array($this, 'after_main_content_sidebar'));

            // Added WooCommerce before main content block
            add_action('woocommerce_before_main_content', 'jankx_open_container', 15);
            add_action('woocommerce_before_main_content', 'jankx_close_container', 30);
        }

        if (apply_filters('jankx_ecommerce_woocommerce_dislabe_loop_add_to_cart', false)) {
            remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart');
        }
    }

    public function before_main_content_sidebar()
    {
        do_action('woocommerce_before_main_content');
    }

    public function after_main_content_sidebar()
    {
        do_action('woocommerce_after_main_content');
    }

    public function cleanWooCommerceStyleSheets($stylesheets)
    {
        if (!apply_filters('jankx_ecommerce_woocommerce_remove_general_stylesheet', true)) {
            return $stylesheets;
        }

        if (isset($stylesheets['woocommerce-general'])) {
            unset($stylesheets['woocommerce-general']);
        }
        if (isset($stylesheets['woocommerce-smallscreen'])) {
            unset($stylesheets['woocommerce-smallscreen']);
        }

        return $stylesheets;
    }

    public function getProductMethod()
    {
        return 'wc_get_product';
    }

    public function registerGlobalVars($data)
    {
        $data['currency'] = get_woocommerce_currency_symbol();

        return $data;
    }

    public function getCartContent($args = array())
    {
        global $woocommerce;
        if (function_exists('woocommerce_mini_cart')) {
            return EcommerceTemplate::render('tpl/cart', array(), null, false);
        }
    }

    public function viewProduct()
    {
        if (!is_woocommerce() || !is_singular('product')) {
            return;
        }
        global $post;
        $viewed_products = array_get($_COOKIE, 'woocommerce_recently_viewed', '');
        $viewed_products = explode('|', $viewed_products);
        if (!in_array($post->ID, $viewed_products)) {
            $viewed_products[] = $post->ID;
        }

        wc_setcookie('woocommerce_recently_viewed', implode('|', $viewed_products));
    }

    public function customizeProductColumns($args)
    {
        if (isset($args['items_per_row'])) {
            wc_set_loop_prop('columns', intval($args['items_per_row']));
        }
    }

    public function getContentGenerator()
    {
        return array(
            'function' => 'wc_get_template_part',
            'args' => array(
                'content',
                'product'
            )
        );
    }

    public function setContentWrapperTagForPostLayout($layoutName, $postLayout)
    {
        $postLayout->setContentGenerator($this->getContentGenerator());
        $postLayout->setContentWrapperTag('ul.products');

        if (is_a($postLayout, Carousel::class)) {
            $postLayout->setItemAsSplide();
        }
    }

    public function customizeArchiveProductPage($page, $templateFile, $templateEngine, $templates, $templateLoader)
    {
        $templates = $page->getTemplates();
        if (!in_array('archive-product', (array)$templates)) {
            return;
        }
        $product_page = get_post(wc_get_page_id('shop'));
        if (!$product_page) {
            return;
        }

        global $wp_query;

        $wp_query->is_post_type_archive = false;
        $wp_query->is_archive = false;
        $wp_query->is_page = true;
        $wp_query->post = $product_page;
        $wp_query->queried_object = $product_page;
        $wp_query->posts = array($product_page);
        $wp_query->post_count = 1;

        $templateLoader->setTemplateFile(false);
        $page->setTemplates($templateLoader->get_page_templates());
    }
}
