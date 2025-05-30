<?php

namespace Jankx\WooCommerce;

use Jankx\GlobalConfigs;
use Jankx\SiteLayout\SiteLayout;
use Jankx\WooCommerce\Abstracts\BaseCustomize;
use Jankx\Woocommerce\Attributes\Database;
use Jankx\WooCommerce\WooCommerceTemplate;
use Jankx\WooCommerce\Traits\WooCommerceData;
use Jankx\PostLayout\Layout\Carousel;
use WC_Product;
use WC_Product_Variable;

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

            add_filter('woocommerce_empty_price_html', [$this, 'customizeEmptyPrice']);
            add_filter('woocommerce_variable_empty_price_html', [$this, 'customizeEmptyPrice']);
            add_filter('woocommerce_grouped_empty_price_html', [$this, 'customizeEmptyPrice']);
        }, 10, 2);

        add_action('woocommerce_before_single_product', function () {
            add_filter('woocommerce_empty_price_html', [$this, 'customizeEmptyPrice']);
            add_filter('woocommerce_variable_empty_price_html', [$this, 'customizeEmptyPrice']);
            add_filter('woocommerce_grouped_empty_price_html', [$this, 'customizeEmptyPrice']);
        });

        add_action('woocommerce_single_product_summary', [$this, 'addedOutOfStockProductContact']);


        // cleanup
        add_action("jankx/layout/product/loop/end", function () use (&$changeThumbnailSize) {
            remove_filter('woocommerce_product_get_image', $changeThumbnailSize, 10);
            remove_filter('single_product_archive_thumbnail_size', [$this, 'changeThumbnailSize']);
            remove_filter('woocommerce_empty_price_html', [$this, 'customizeEmptyPrice']);
            remove_filter('woocommerce_variable_empty_price_html', [$this, 'customizeEmptyPrice']);
            remove_filter('woocommerce_grouped_empty_price_html', [$this, 'customizeEmptyPrice']);
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


        // Integrate with Jankx WooCommerce Attributes
        if (defined('JANKX_WOO_ATTRIBUTES_MAIN_FILE')) {
            add_action("jankx/posts/fetcher/product/query/start", [$this, 'registerJankxWooCommerceAttributeHooks'], 10, 2);
            add_action("jankx/posts/fetcher/product/query/end", [$this, 'renoveJankxWooCommerceAttributeHooks'], 10, 2);
        }
        if (GlobalConfigs::get('customs.woocommerce.sales.flash_percent', false)) {
            add_filter('woocommerce_sale_flash', [$this, 'add_percentage_to_sale_badge'], 20, 3);
        }
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

        return !static::$disableShopSidebar;
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
        $jankxTemplate = sprintf('woocommerce/%s', str_replace('.php', '', $template_name));

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
        if (!in_array('archive-product', (array) $templates) || jankx_is_support_block_template()) {
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


    protected function processingMetaPrice($meta_prices)
    {
        $price_conditions = [];
        if (!is_array($meta_prices)) {
            $meta_prices = [$meta_prices];
        }
        if (count($meta_prices) > 1) {
            $price_conditions['relation'] = 'OR';
        }

        foreach ($meta_prices as $meta_price) {
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
        return $price_conditions;
    }

    protected function processingProductAttributes($attributeConditions, $attribute)
    {
        $conditions = [];
        if (!is_array($attributeConditions)) {
            $attributeConditions = [$attributeConditions];
        }

        if (count($attributeConditions) > 1) {
            $conditions['relation'] = 'OR';
        }

        foreach ($attributeConditions as $attributeCondition) {
            $condition = [];
            $conditionArr = explode('-', $attributeCondition);
            if (count($conditionArr) > 1) {
                $condition['relation'] = 'AND';
            }
            $condition[] = [
                'key' => $attribute,
                'value' => $conditionArr[0],
                'compare' => '>=',
                'type' => 'NUMERIC'
            ];

            if (isset($conditionArr[1])) {
                $condition[] = [
                    'key' => $attribute,
                    'value' => $conditionArr[1],
                    'compare' => '<',
                    'type' => 'NUMERIC'
                ];
            }
            $conditions[] = $condition;
        }
        return $conditions;
    }

    public function filterProductArgsByRequest($args, $originRequest, $data_preset, $postFetcher)
    {
        if (!empty($originRequest['meta'])) {
            $args['meta_query'] = [
                'relation' => 'AND'
            ];

            foreach ($originRequest['meta'] as $metaType => $meta) {
                switch ($metaType) {
                    case 'meta_price':
                        $args['meta_query'][] = $this->processingMetaPrice($meta);
                        break;
                    default:
                        if (strpos($metaType, 'attribute_') !== false) {
                            $conditions = $this->processingProductAttributes($meta, $metaType);
                            if (!empty($conditions)) {
                                $args['meta_query'][] = $conditions;
                            }
                        }
                        break;
                }
            }
        }
        return $args;
    }


    public function registerJankxWooCommerceAttributeHooks($args, $postFetcher)
    {
        add_filter('posts_join', [$this, 'filterJoinByWoocommerceAttributeCustomTable'], 10, 2);
        add_filter('posts_where', [$this, 'filterWhereByWoocommerceAttributeCustomTable'], 10, 2);
    }
    public function removeJankxWooCommerceAttributeHooks($args, $postFetcher)
    {
        remove_filter('posts_join', [$this, 'filterJoinByWoocommerceAttributeCustomTable'], 10);
        remove_filter('posts_where', [$this, 'filterWhereByWoocommerceAttributeCustomTable'], 10);
        die('yeah');
    }


    public function filterJoinByWoocommerceAttributeCustomTable($join)
    {
        if (strpos($join, 'INNER JOIN xvn2_postmeta ON ( xvn2_posts.ID = xvn2_postmeta.post_id )') === false) {
            return $join;
        }
        $wpdb = Database::getWpdb();

        $join = str_replace([
            sprintf('%s.post_id', $wpdb->postmeta),
            $wpdb->postmeta,
            'xvn2_jankx_woo_attributes.post_id'
        ], [
            sprintf('%s.product_id', Database::getAttributeTable()),
            Database::getAttributeTable(),
        ], $join);

        $join = preg_replace_callback('/mt(\d{1,})\.post_id/', function ($matches) {
            return sprintf('mt%d.product_id', $matches[1]);
        }, $join);

        return $join;
    }

    public function filterWhereByWoocommerceAttributeCustomTable($where)
    {
        $wpdb = Database::getWpdb();
        if (strpos($where, sprintf('%s.meta_key = \'attribute_', $wpdb->postmeta)) === false) {
            return $where;
        }

        $where = str_replace([
            sprintf('%s.meta_key = \'attribute', $wpdb->postmeta),
            sprintf('%s.meta_value', $wpdb->postmeta),
        ], [
            sprintf('%s.meta_key = \'attribute', Database::getAttributeTable()),
            sprintf('%s.value', Database::getAttributeTable()),
        ], $where);

        if (preg_match_all("/meta_key\s?=\s?\'attribute_([^\']{1,})\'/", $where, $matches)) {
            $originMetas = array_unique($matches[0]);
            $attributes = array_unique($matches[1]);
            $replaceAttributes = array_map(function ($attribute) {
                return sprintf('attribute = \'%s\'', $attribute);
            }, $attributes);

            $where = str_replace($originMetas, $replaceAttributes, $where);
        }

        $where = preg_replace_callback('/mt(\d{1,})\.meta_value/', function ($matches) {
            return sprintf('mt%d.value', $matches[1]);
        }, $where);

        return $where;
    }


    public function customizeEmptyPrice()
    {
        $emptyPrice = GlobalConfigs::get('customs.woocommerce.price.empty', '');

        return WooCommerceTemplate::render('loop/empty-price', [
            'text' => $emptyPrice
        ], false);
    }

    public function add_percentage_to_sale_badge($html, $post, $product)
    {

        if ($product->is_type('variable')) {
            $percentages = array();

            // Get all variation prices
            $prices = $product->get_variation_prices();

            // Loop through variation prices
            foreach ($prices['price'] as $key => $price) {
                // Only on sale variations
                if ($prices['regular_price'][$key] !== $price) {
                    // Calculate and set in the array the percentage for each variation on sale
                    $percentages[] = round(100 - (floatval($prices['sale_price'][$key]) / floatval($prices['regular_price'][$key]) * 100));
                }
            }
            // We keep the highest value
            $percentage = max($percentages) . '%';
        } elseif ($product->is_type('grouped')) {
            $percentages = array();

            // Get all variation prices
            $children_ids = $product->get_children();

            // Loop through variation prices
            foreach ($children_ids as $child_id) {
                $child_product = wc_get_product($child_id);

                $regular_price = (float) $child_product->get_regular_price();
                $sale_price = (float) $child_product->get_sale_price();

                if ($sale_price != 0 || !empty($sale_price)) {
                    // Calculate and set in the array the percentage for each child on sale
                    $percentages[] = round(100 - ($sale_price / $regular_price * 100));
                }
            }
            // We keep the highest value
            $percentage = max($percentages) . '%';
        } else {
            $regular_price = (float) $product->get_regular_price();
            $sale_price = (float) $product->get_sale_price();

            if ($sale_price != 0 || !empty($sale_price)) {
                $percentage = round(100 - ($sale_price / $regular_price * 100)) . '%';
            } else {
                return $html;
            }
        }

        return WooCommerceTemplate::render('loop/onsale_percent', [
            'percentage' => $percentage,
            'text' => esc_html__('SALE', 'woocommerce'),
        ], false);
    }


    public function addedOutOfStockProductContact() {
        global $product;

        if ($product instanceof WC_Product && !$product->is_purchasable() && !$product instanceof WC_Product_Variable) {
            echo '<form class="variations_form cart">';
                jankx_woocommerce_template('single-product/contact_button', []);
            echo '</form>';
        }
    }
}
