<?php
namespace Jankx\Ecommerce\Integration;

use Jankx\Ecommerce\Integration\Elementor\Elementor;

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
            $this->integratedPlugins[Elementor::class] = array();
        }
    }

    public function getIntegratedPlugins()
    {
        return array_keys($this->integratedPlugins);
    }

    public function integrate()
    {
        foreach ($this->getIntegratedPlugins() as $integrateClassName) {
            if (empty($this->integratedPlugins[$integrateClassName])) {
                $this->integratedPlugins[$integrateClassName] = new $integrateClassName();
            }
        }
    }
}
