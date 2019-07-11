<?php
namespace Jankx\Ecommerce;

class Manager
{
    protected static $instance;
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
        $this->detecter = new PluginDetecter();

        add_action('after_setup_theme', array($this->detecter, 'getECommercePlugin'));
        add_action('after_setup_theme', array($this, 'loadFeatures'));
    }

    public function loadFeatures()
    {
        $this->pluginName = $this->detecter->getPlugin();

        if (empty($this->pluginName) || !class_exists($className = sprintf('%s\Plugins\%s', __NAMESPACE__, $this->pluginName))) {
            return;
        }
        $this->plugin = new $className();
    }

    public function getPlugin()
    {
        return $this->plugin;
    }
}