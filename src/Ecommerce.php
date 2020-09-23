<?php
namespace Jankx\Ecommerce;

use Jankx\Ecommerce\Plugin\WooCommerce;
use Jankx\Ecommerce\Base\Component\CartButton;
use Jankx\Ecommerce\Integration\Plugin;
use Jankx\Ecommerce\Base\MenuItems;

class Ecommerce
{
    const NAME = 'jankx-ecommerce';
    const VERSION = '1.0.0';

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
        add_filter('jankx_template_css_dependences', array($this, 'registerScripts'));
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

    public function registerScripts($handles)
    {
        css(static::NAME, jankx_ecommerce_asset_url('css/ecommerce.css'), array(), static::VERSION);

        array_push($handles, static::NAME);

        return $handles;
    }
}
