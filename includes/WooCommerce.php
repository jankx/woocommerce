<?php
namespace Jankx\WooCommerce;

use Jankx\WooCommerce\Plugin\WooCommerce;
use Jankx\WooCommerce\Base\Component\CartButton;
use Jankx\WooCommerce\Integration\Plugin;
use Jankx\WooCommerce\Base\MenuItems;
use Jankx\WooCommerce\Base\Rest\RestManager;
use Jankx\WooCommerce\Base\Layouts\ProductInfoTopWithSidebar;
use Jankx\WooCommerce\Base\Layouts\ProductInfoTopWithSidebarBellowName;
use Jankx\PostLayout\PostLayoutManager;
use Jankx\WooCommerce\WooCommerceTemplate;

class WooCommerce
{
    const NAME = 'jankx-ecommerce';
    const VERSION = '1.0.0.1';

    protected static $instance;
    protected static $supportPlugins;
    protected static $singleProductLayouts;


    protected $detecter;
    protected $shopPlugin;
    protected $pluginName;

    protected $ecommerceMenu;

    protected $menu;

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        static::$supportPlugins = array(
            WooCommerce::PLUGIN_NAME => WooCommerce::class,
        );
        $this->bootstrap();
        $this->loadHelpers();

        $this->detecter = new PluginDetecter();
        $this->ecommerceMenu = new MenuItems();

        add_action('after_setup_theme', array(
            $this->detecter,
            'getECommercePlugin'
        ));
        add_action('after_setup_theme', array(Plugin::class, 'getInstance'));
        add_action('after_setup_theme', array($this, 'loadFeatures'));
        add_action('after_setup_theme', array($this, 'loadSupportLayouts'), 20);
        add_action('after_setup_theme', array($this, 'setupShopLayout'), 30);

        add_action('wp_enqueue_scripts', array($this, 'registerScripts'), 15);

        add_filter('jankx_template_css_dependences', array($this, 'registerWooCommerceStylesheet'));
    }

    private function bootstrap()
    {
        define('JANKX_WOOCOMMERCE_ROOT_DIR', dirname(__DIR__));
    }

    public function loadFeatures()
    {
        $this->pluginName = $this->detecter->getPlugin();

        $this->ecommerceMenu->register();

        if (empty($this->pluginName) || !isset(static::$supportPlugins[$this->pluginName])) {
            return;
        }
        $className = static::$supportPlugins[$this->pluginName];
        $this->shopPlugin = new $className();

        // Register rest endpoints
        RestManager::getInstance();

        add_theme_support('render_js_template');
        add_theme_support('woocommerce');

        add_filter('jankx_components', array($this, 'registerWooCommerceComponents'));
        add_action('wp', array($this->shopPlugin, 'viewProduct'));
    }

    public function getShopPlugin()
    {
        return $this->shopPlugin;
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
            jankx_woocommerce_asset_url('js/ecommerce.js'),
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
        if (!is_null(static::$singleProductLayouts)) {
            return static::$singleProductLayouts;
        }

        static::$singleProductLayouts = apply_filters('jankx_woocommerce_woocommerce_single_layouts', array(
            ProductInfoTopWithSidebar::LAYOUT_NAME => ProductInfoTopWithSidebar::class,
            ProductInfoTopWithSidebarBellowName::LAYOUT_NAME => ProductInfoTopWithSidebarBellowName::class,
        ));

        return static::$singleProductLayouts;
    }

    public function setupShopLayout()
    {
        $singleProductLayout = jankx_woocommerce_single_product_layout();
        if ($singleProductLayout && $singleProductLayout !== 'default') {
            if (isset(static::$singleProductLayouts[$singleProductLayout]) && class_exists(static::$singleProductLayouts[$singleProductLayout])) {
                new static::$singleProductLayouts[$singleProductLayout]();
            }
        }

        $engine = WooCommerceTemplate::getEngine();
        PostLayoutManager::createInstance($engine);
    }
}
