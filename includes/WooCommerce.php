<?php

namespace Jankx\WooCommerce;

use Jankx\WooCommerce\Customize as WooCommercePlugin;
use Jankx\WooCommerce\Component\CartButton;
use Jankx\WooCommerce\Integration\Plugin;
use Jankx\WooCommerce\MenuItems;
use Jankx\WooCommerce\Rest\RestManager;
use Jankx\PostLayout\PostLayoutManager;
use Jankx\WooCommerce\Layouts\Loop\AddCartButtonInThumbnailWrap;
use Jankx\WooCommerce\Layouts\Loop\DetailAndBuyNowButton;
use Jankx\WooCommerce\Layouts\ProductDetail\NoSidebar\ImageAndProductInfosOnTopDescriptionBellow;
use Jankx\WooCommerce\Layouts\ProductSummary\ProductVariationChooserAndInputSpinner;
use Jankx\WooCommerce\WooCommerceTemplate;

class WooCommerce
{
    const NAME = 'jankx-ecommerce';
    const VERSION = '1.0.21';

    protected static $instance;
    protected static $singleProductLayouts;
    protected static $productSummaryLayouts = [];

    protected $detecter;
    protected $wooCommerceCustomizer;
    protected $pluginName;

    protected $ecommerceMenu;

    protected $detailProductLayout;

    protected $menu;

    /**
     * Summary of instance
     * @return WooCommerce
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->bootstrap();
        $this->loadHelpers();

        $this->ecommerceMenu = new MenuItems();
        $this->wooCommerceCustomizer = new WooCommercePlugin();

        add_action('after_setup_theme', array(Plugin::class, 'getInstance'));
        add_action('after_setup_theme', array($this, 'loadFeatures'));
        add_action('after_setup_theme', array($this, 'setupShopLayout'), 30);


        // Single products
        add_action('wp', array($this, 'loadSupportLayouts'), 20);
        add_action('wp', [$this, 'loadSingleProductLayout'], 30);

        add_action('wp_enqueue_scripts', array($this, 'registerScripts'), 15);

        add_filter('jankx_template_css_dependences', array($this, 'registerWooCommerceStylesheet'));
    }

    private function bootstrap()
    {
        define('JANKX_WOOCOMMERCE_ROOT_DIR', dirname(__DIR__));
    }

    public function loadFeatures()
    {
        $this->pluginName = WooCommercePlugin::PLUGIN_NAME;

        $this->ecommerceMenu->register();


        // Register rest endpoints
        RestManager::getInstance();

        add_theme_support('render_js_template');
        add_theme_support('woocommerce');

        add_filter('jankx_components', array($this, 'registerWooCommerceComponents'));
        add_action('wp', array($this->wooCommerceCustomizer, 'viewProduct'));
    }

    public function getShopPlugin()
    {
        return $this->wooCommerceCustomizer;
    }

    public function registerWooCommerceComponents($components)
    {
        if (!isset($components[CartButton::COMPONENT_NAME])) {
            $components[CartButton::COMPONENT_NAME] = CartButton::class;
        } else {
            throw new \Exception(sprintf('Component %s is already exists', CartButton::COMPONENT_NAME));
        }

        return $components;
    }

    public function loadHelpers()
    {
        require_once dirname(JANKX_WOOCOMMERCE_FILE_LOADER) . '/helpers/functions.php';
    }

    public function registerWooCommerceStylesheet($handles)
    {
        css(static::NAME, jankx_woocommerce_asset_url('css/ecommerce.css'), array(), static::VERSION);

        array_push($handles, static::NAME);

        return $handles;
    }

    public function registerScripts()
    {
        $deps = array( 'popperjs', 'fslightbox' );

        // Register script
        js(
            static::NAME,
            [
                'url' => jankx_woocommerce_asset_url('js/woocommerce.js'),
                'url.min' => jankx_woocommerce_asset_url('js/woocommerce.min.js')
            ],
            apply_filters('jankx/woocommerce/js/dependences', $deps),
            static::VERSION,
            true
        )->localize(
            'jankx_woocommerce',
            apply_filters(
                'jankx_woocommerce_localize_object_data',
                array(
                    'get_product_url' => rest_url('jankx/v1/ecommerce/get_products'),
                    'errors' => array(
                        'get_data_error' => __('Get data has exception', 'jankx_woocommerce'),
                        'parse_data_error' => __('Parse the data has exception', 'jankx_woocommerce'),
                    )
                )
            )
        )
        ->enqueue();

        $deps = array();
        $styleMetadata = get_file_data(
            sprintf('%s/assets/css/ecommerce.css', dirname(JANKX_WOOCOMMERCE_FILE_LOADER)),
            array(
                'version' => 'Version',
            )
        );

        css(
            static::NAME,
            jankx_woocommerce_asset_url('css/ecommerce.css'),
            apply_filters('jankx/woocommerce/css/dependences', $deps),
            empty($styleMetadata['version']) ? static::VERSION : $styleMetadata['version']
        )->enqueue();
    }


    public function loadSupportLayouts()
    {
        if (!is_singular('product')) {
            return;
        }

        if (!is_null(static::$singleProductLayouts)) {
            return static::$singleProductLayouts;
        }

        static::$singleProductLayouts = apply_filters('jankx_woocommerce_woocommerce_single_layouts', array(
            'default' => ImageAndProductInfosOnTopDescriptionBellow::class,
        ));
        return static::$singleProductLayouts;
    }

    public function loadSingleProductLayout()
    {
        if (!is_singular('product')) {
            return;
        }
        $singleProductLayout = jankx_woocommerce_single_product_layout();
        if (isset(static::$singleProductLayouts[$singleProductLayout]) && class_exists(static::$singleProductLayouts[$singleProductLayout])) {
            $this->detailProductLayout = new static::$singleProductLayouts[$singleProductLayout]();
        }
    }

    public function getDefaultLoopItemLayout()
    {
        return apply_filters('jankx/woocommerce/loop_item/layout', DetailAndBuyNowButton::getType());
    }

    public function setupShopLayout()
    {
        // Setup templates
        $engine = WooCommerceTemplate::getEngine();
        PostLayoutManager::createInstance($engine);

        add_filter('jankx/posts/loop/layouts', function ($loopItemLayouts) {
            return array_merge(
                $loopItemLayouts,
                [
                    DetailAndBuyNowButton::getType() => DetailAndBuyNowButton::class,
                    AddCartButtonInThumbnailWrap::LOOP_LAYOUT_NAME => AddCartButtonInThumbnailWrap::class
                ]
            );
        });
    }


    public function getProductSummaryLayouts()
    {
        if (!is_singular('product')) {
            return [];
        }

        if (!empty(static::$productSummaryLayouts)) {
            return static::$productSummaryLayouts;
        }

        static::$productSummaryLayouts = apply_filters('jankx/woocommerce/product/summary/layouts', [
            ProductVariationChooserAndInputSpinner::NAME => ProductVariationChooserAndInputSpinner::class,
        ]);

        return static::$productSummaryLayouts;
    }
}
