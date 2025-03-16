<?php

namespace Jankx\WooCommerce;

use Jankx\GlobalConfigs;
use Jankx\SiteLayout\SiteLayout;
use Jankx\WooCommerce\Abstracts\BaseCustomize;
use Jankx\WooCommerce\WooCommerceTemplate;
use Jankx\WooCommerce\Traits\WooCommerceData;
use Jankx\PostLayout\Layout\Carousel;

class Customize extends BaseCustomize
{
    use WooCommerceData;

    const PLUGIN_NAME = 'woocommerce';

    protected static $disableShopSidebar;

    protected $shopSidebarHook;

    protected $jsURLModified = false;

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

        add_action("jankx/woocommerce/loop/before", array($this, 'customizeProductColumns'));

        add_action('jankx/layout/product/loop/start', function ($layout_name, $postLayoutInstance) {
            if (in_array($layout_name, array(Carousel::LAYOUT_NAME))) {
                return;
            }

            wc_set_loop_prop('columns', $postLayoutInstance->getOption('columns'));
            woocommerce_product_loop_start();
        }, 10, 2);


        $changeThumbnailSize = null;

        add_action("jankx/layout/product/loop/start", function ($layoutName, $layoutInstance) use (&$changeThumbnailSize) {
            $changeThumbnailSize = function ($size) use ($layoutName, $layoutInstance) {
                if (($optionSize = $layoutInstance->getOption('thumbnail_size')) !== 'woocommerce_thumbnail') {
                    if ($optionSize !== 'custom') {
                        return $optionSize;
                    }
                    return sprintf('%sx%s', $layoutInstance->getOption('image_width', 300), $layoutInstance->getOption('image_height', 300));
                }
                return $size;
            };
            add_filter('single_product_archive_thumbnail_size', $changeThumbnailSize);
            add_filter('woocommerce_product_get_image', [$this, 'createImageWrapper'], 10, 4);
        }, 10, 2);

        add_action("jankx/layout/product/loop/end", function () use (&$changeThumbnailSize) {
            remove_filter('woocommerce_product_get_image', $changeThumbnailSize, 10);
            remove_filter('single_product_archive_thumbnail_size', [$this, 'changeThumbnailSize']);
        }, 10, 2);

        add_action('jankx/layout/product/loop/end', function ($layout) {
            if (in_array($layout, array(Carousel::LAYOUT_NAME))) {
                return;
            }
            woocommerce_product_loop_end();
        });

        add_action('jankx/layout/product/loop/init', array($this, 'setContentWrapperTagForPostLayout'), 10, 2);


        add_filter('jankx/posts/fetcher/product/content_layout', function () {
            return WooCommerce::instance()->getDefaultLoopItemLayout();
        });

        add_filter('jankx/layout/product/args', [$this, 'filterProductArgsByRequest'], 10, 4);
    }

    public function init()
    {
        add_action('jankx/template/site/layout', array($this, 'customShopLayout'));
        add_action('jankx_template_page_single_product', array($this, 'renderProductContent'));

        // Custom WooCommercce templates
        add_filter('wc_get_template', array($this, 'changeWooCommerceTemplates'), 10, 5);

        // Make WooCommerce is global
        add_filter('body_class', array($this, 'addWoocommerceCSSBodyClass'));

        add_filter('template_include', array($this, 'loadCustomWooCommerceTemplates'), 15);
        add_action('template_redirect', array($this, 'customWooCommerceElements'));
        add_action('woocommerce_enqueue_styles', array($this, 'cleanWooCommerceStyleSheets'));
        add_filter('jankx_woocommerce_localize_object_data', array($this, 'registerGlobalVars'));

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
            'jankx_woocommerce_global_sidebar_args',
            $shopSidebarArgs
        ));

        if (GlobalConfigs::get('customs.woocommerce.product.sidebar', true)) {
            $shopSidebarArgs['id'] = 'product_detail';
            $shopSidebarArgs['name'] = __('Product Details Sidebar', 'jankx');

            register_sidebar(apply_filters(
                'jankx_woocommerce_product_sidebar_args',
                $shopSidebarArgs
            ));
        }
    }

    protected function checkSidebarIsActive()
    {
        if (is_null(static::$disableShopSidebar)) {
            $siteLayout = SiteLayout::getInstance();
            static::$disableShopSidebar = apply_filters(
                'jankx_woocommerce_disable_shop_sidebar',
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

            remove_action('jankx/template/main_content/after', 'get_sidebar', 35);
            remove_action('jankx/template/main_content/after', array($layoutLoader, 'loadSecondarySidebar'), 45);

            if ($this->checkSidebarIsActive()) {
                $this->shopSidebarHook = apply_filters(
                    'jankx/woocommerce/woocommerce/sidebar/hook_loader',
                    'jankx/template/main_content/after',
                    $layoutLoader
                );
                add_action($this->shopSidebarHook, array($this, 'createWooCommerceSidebar'), 35);
                add_action('jankx_sidebar_shop_content', array($this, 'renderShopSidebar'));
            }

            if (GlobalConfigs::get('customs.woocommerce.product.sidebar', true)) {
                add_action('woocommerce_after_single_product_summary', [$this, 'openProductContentSidebarWrap'], 8);
                add_action('woocommerce_after_single_product_summary', [$this, 'closeProductContentSidebarWrap'], 14);
            }
        }
    }

    public function openProductContentSidebarWrap()
    {
        printf('<div %s>', jankx_generate_html_attributes([
            'class' => 'jankx-product-content-sidebar-wrap',
            'id' => 'jankx-shop-content-sidebar'
        ]));

        printf('<div %s>', jankx_generate_html_attributes([
            'class' => 'jankx-product-content-wrap',
        ]));
    }
    public function closeProductContentSidebarWrap()
    {
        echo '</div><!-- end .jankx-product-content-wrap -->';

        printf('<div %s>', jankx_generate_html_attributes([
            'class' => ['jankx-product-sidebar']
        ]));
        dynamic_sidebar('product_detail');
        echo '</div><!-- end .jankx-product-sidebar -->';
        echo '</div><!-- end #jankx-shop-content-sidebar -->';
    }

    public function createWooCommerceSidebar()
    {
        do_action('woocommerce_sidebar');
    }

    public function changeDefaultSiteLayoutSingleProduct($layout)
    {
        if (is_woocommerce()) {
            if (!is_single()) {
                return $layout;
            }
            $sidebarPosition = apply_filters('jankx/woocommerce/product/detail/sidebar', 'right');
            if ($sidebarPosition === 'right') {
                return SiteLayout::LAYOUT_CONTENT_SIDEBAR;
            }
            if ($sidebarPosition === 'left') {
                return SiteLayout::LAYOUT_SIDEBAR_CONTENT;
            }
        }
        return $layout;
    }

    public function renderShopSidebar()
    {
        if ($this->shopSidebarHook) {
            return WooCommerceTemplate::render('woocommerce/shop-sidebar');
        }
    }

    public function renderProductContent()
    {
        return WooCommerceTemplate::render(
            $this->getName() . '/single-product'
        );
    }

    public function changeWooCommerceTemplates($template, $template_name, $args, $template_path, $default_path)
    {
        $jankxTemplate    = sprintf('woocommerce/%s', rtrim($template_name, '.php'));
        $searchedTemplate = WooCommerceTemplate::search($jankxTemplate);
        // Return Jankx WooCommerce template when the template is existing
        if ($searchedTemplate) {
            return $searchedTemplate;
        }

        // Return default WooCommerce template when Jankx WooCommerce template is not found`
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
        if (strpos($template, sprintf(implode(DIRECTORY_SEPARATOR, ['', 'plugins', 'woocommerce']))) !== false) {
            $t = null;
            if (is_singular('product')) {
                $t = 'woocommerce/single-product';
            } elseif (is_product_taxonomy()) {
                $t = 'woocommerce/archive-product';
            }

            if (!is_null($t)) {
                $searchedTemplate = WooCommerceTemplate::search($t);
                if ($searchedTemplate) {
                    return $searchedTemplate;
                }
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

            add_action('jankx/template/header/after', array($this, 'before_main_content_sidebar'), 16);
            add_action('jankx/template/main_content/after_sidebar', array($this, 'after_main_content_sidebar'));

            // Added WooCommerce before main content block
            add_action('woocommerce_before_main_content', 'jankx_open_container', 15);
            add_action('woocommerce_before_main_content', 'jankx_close_container', 30);
        } else {
            add_action('jankx/template/header/after', 'woocommerce_output_all_notices', 18);
        }

        if (apply_filters('jankx_woocommerce_woocommerce_dislabe_loop_add_to_cart', false)) {
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
        if (!apply_filters('jankx_woocommerce_woocommerce_remove_general_stylesheet', true)) {
            return $stylesheets;
        }

        if (isset($stylesheets['woocommerce-smallscreen'])) {
            unset($stylesheets['woocommerce-smallscreen']);
        }

        if (isset($stylesheets['woocommerce-layout'])) {
            unset($stylesheets['woocommerce-layout']);
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
            return WooCommerceTemplate::render('tpl/cart', array(), null, false);
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
        $postLayout->setContentWrapperTag('ul.product-list,ul.products');
    }

    public function customizeArchiveProductPage($page, $templateFile, $templateEngine, $templates, $templateLoader)
    {
        $templates = $page->getTemplates();
        if (!in_array('archive-product', (array)$templates) || jankx_is_support_block_template()) {
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

    public function createImageWrapper($image, $wc_product, $size, $attr)
    {
        return jankx_template('post-layout/thumbnail', [
            'post' => $wc_product,
            'data_index' => 0,
            'thumbnail_size' => $size,
            'content' => $image
        ], false);
    }


    public function filterProductArgsByRequest($args, $originRequest, $data_preset, $postFetcher)
    {
        if (!empty($originRequest['meta'])) {
            $args['meta_query'] = [
                'relation' => 'AND'
            ];

            if (isset($originRequest['meta']['meta_price'])) {
                $meta_prices = $originRequest['meta']['meta_price'];
                $price_conditions = [];
                if (!is_array($meta_prices)) {
                    $meta_prices = [$meta_prices];
                }
                if (count($meta_prices) > 1) {
                    $price_conditions['relation'] = 'OR';
                }
                foreach( $meta_prices as $meta_price ) {
                    $condition = [];
                    $priceArr = explode('-', $meta_price);
                    if (count($priceArr) > 1) {
                        $condition['relation'] = 'AND';
                    }
                    $condition[] = [
                        'key' => '_price',
                        'value' => $priceArr[0],
                        'compare' => '>=',
                        'type' => 'NUMERIC'
                    ];

                    if (isset($priceArr[1])) {
                        $condition[] = [
                            'key' => '_price',
                            'value' => $priceArr[1],
                            'compare' => '<',
                            'type' => 'NUMERIC'
                        ];
                    }
                    $price_conditions[] = $condition;
                }
                $args['meta_query'][] = $price_conditions;
            }
        }
        return $args;
    }
}
