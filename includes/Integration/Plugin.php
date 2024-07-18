<?php

namespace Jankx\WooCommerce\Integration;

use Jankx\WooCommerce\Integration\Elementor\Elementor;

class Plugin
{
    protected static $instance;
    protected static $activePlugins;

    protected $integratedPlugins = array();

    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    private function __construct()
    {
        $this->detectPlugins();
        $this->integrate();
    }

    protected function detectPlugins()
    {
        static::$activePlugins = get_option('active_plugins');
        if (in_array('elementor/elementor.php', static::$activePlugins)) {
            $this->integratedPlugins['elementor/elementor.php'] = Elementor::class;
        }
    }

    public function getIntegratedPlugins()
    {
        return apply_filters(
            'jankx_woocommerce_plugin_integrations',
            $this->integratedPlugins
        );
    }

    public function integrate()
    {
        foreach ($this->getIntegratedPlugins() as $pluginName => $integrateClassName) {
            if (class_exists($integrateClassName)) {
                $this->integratedPlugins[$integrateClassName] = new $integrateClassName();
            } else {
                throw new \Exception(sprintf(
                    'Class %s is not found to integrate with plugin %s.',
                    $integrateClassName,
                    $pluginName
                ));
            }
        }
    }
}
