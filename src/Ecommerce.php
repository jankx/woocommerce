<?php
namespace Jankx\Ecommerce;

use Jankx\Ecommerce\Plugin\WooCommerce;
use Jankx\Ecommerce\Component\CartButton;
use Jankx\Ecommerce\Integration\Plugin;
use Jankx\Ecommerce\Template;

class Ecommerce
{
    protected static $instance;
    protected static $supportPlugins;

    protected $detecter;
    protected $shopPlugin;
    protected $pluginName;

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
        $this->detecter = new PluginDetecter();

        add_action('after_setup_theme', array(
            $this->detecter,
            'getECommercePlugin'
        ));
        add_action('template_redirect', array(Template::class, 'hookTemplates'));
        add_action('after_setup_theme', array(Plugin::class, 'getInstance'));
        add_action('after_setup_theme', array($this, 'loadFeatures'));
    }

    public function loadFeatures()
    {
        $this->pluginName = $this->detecter->getPlugin();

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
}
