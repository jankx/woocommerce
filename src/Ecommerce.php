<?php
namespace Jankx\Ecommerce;

use Jankx\Ecommerce\Plugin\WooCommerce;
use Jankx\Ecommerce\Base\Component\CartButton;
use Jankx\Ecommerce\Integration\Plugin;
use Jankx\Ecommerce\Base\MenuItems;
use Jankx\Ecommerce\Base\Rest\RestManager;

class Ecommerce
{
    const NAME = 'jankx-ecommerce';
    const VERSION = '1.0.1';

    protected static $instance;
    protected static $supportPlugins;

    protected $detecter;
    protected $shopPlugin;
    protected $pluginName;
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
        $this->loadHelpers();

        $this->detecter = new PluginDetecter();
        $this->ecommerceMenu = new MenuItems();

        add_action('after_setup_theme', array(
            $this->detecter,
            'getECommercePlugin'
        ));
        add_action('after_setup_theme', array(Plugin::class, 'getInstance'));
        add_action('after_setup_theme', array($this, 'loadFeatures'));

        add_filter('jankx_template_css_dependences', array($this, 'registerEcommerceStylesheet'));
        add_action('wp_enqueue_scripts', array($this, 'registerScripts'));
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

        add_filter('jankx_components', array($this, 'registerEcommerceComponents'));
    }

    public function getShopPlugin()
    {
        return $this->shopPlugin;
    }

    public function registerEcommerceComponents($components)
    {
        if (!isset($components[CartButton::getName()])) {
            $components[CartButton::getName()] = CartButton::class;
        } else {
            throw new \Exception(sprintf('Component %s is already exists', CartButton::getName()));
        }

        return $components;
    }

    public function loadHelpers()
    {
        require_once dirname(JANKX_ECOMMERCE_FILE_LOADER) . '/helpers/functions.php';
    }

    public function registerEcommerceStylesheet($handles)
    {
        css(static::NAME, jankx_ecommerce_asset_url('css/ecommerce.css'), array(), static::VERSION);

        array_push($handles, static::NAME);

        return $handles;
    }

    public function registerScripts()
    {
        // Register script
        wp_register_script(
            static::NAME,
            jankx_ecommerce_asset_url('js/ecommerce.js'),
            array( 'tether-drop' ),
            static::VERSION,
            true
        );
        wp_localize_script(static::NAME, 'jankx_ecommerce', apply_filters(
            'jankx_ecommerce_localize_object_data',
            array(
                'get_product_url' => rest_url('jankx/v1/ecommerce/get_products'),
                'errors' => array(
                    'get_data_error' => __('Get data has exception', 'jankx_ecommerce'),
                    'parse_data_error' => __('Parse the data has exception', 'jankx_ecommerce'),
                )
            )
        ));
        // Call the scripts
        wp_enqueue_script(static::NAME);

        $styleMetadata = get_file_data(
            sprintf('%s/assets/css/ecommerce.css', dirname(JANKX_ECOMMERCE_FILE_LOADER)),
            array(
                'version' => 'Version',
            )
        );
        wp_register_style(
            static::NAME,
            jankx_ecommerce_asset_url('css/ecommerce.css'),
            array( 'tether-drop' ),
            empty($styleMetadata['version']) ? static::VERSION : $styleMetadata['version']
        );
        wp_enqueue_style(static::NAME);
    }
}
