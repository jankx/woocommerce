<?php
namespace Jankx\Ecommerce;

use Jankx\Ecommerce\Plugin\WooCommerce;

class Ecommerce
{
    protected static $instance;
    protected static $supportPlugins;

    protected $detecter;
    protected $plugin;
    protected $pluginName;

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        static::$supportPlugins = array(
            WooCommerce::PLUGIN_NAME => WooCommerce::class,
        );
        $this->detecter = new PluginDetecter();

        add_action('after_setup_theme', array($this->detecter, 'getECommercePlugin'));
        add_action('after_setup_theme', array($this, 'loadFeatures'));
    }

    public function loadFeatures()
    {
        $this->pluginName = $this->detecter->getPlugin();

        if (empty($this->pluginName) || !isset(static::$supportPlugins[$this->pluginName])) {
            return;
        }
        $className = static::$supportPlugins[$this->pluginName];
        $this->plugin = new $className();
    }

    public function getPlugin()
    {
        return $this->plugin;
    }
}
