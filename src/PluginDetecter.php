<?php
namespace Jankx\Ecommerce;

use Jankx\Ecommerce\Plugin\WooCommerce;

class PluginDetecter
{
    protected $detectPluginRules;
    protected $detectedPlugin;

    public function __construct()
    {
        $defaultRules = array(
            WooCommerce::PLUGIN_NAME => array(
                'classes' => array(
                    'WooCommerce',
                    'WC_Product',
                ),
                'constants' => array(
                    'WOOCOMMERCE_VERSION',
                    'WC_ABSPATH',
                ),
            )
        );
        $this->detectPluginRules = apply_filters('jankx_ecommerce_detect_plugin_rules', $defaultRules);
    }

    public function getECommercePlugin()
    {
        foreach ($this->detectPluginRules as $pluginName => $rules) {
            if ($this->parseRules($rules)) {
                $this->pluginDetected = trim($pluginName);
                return $this->pluginDetected;
            }
        }
    }

    public function parseRules($rules)
    {
        $found = true;
        if (isset($rules['classes'])) {
            foreach ((array)$rules['classes'] as $className) {
                if (!class_exists($className)) {
                    return false;
                }
            }
        }
        if (isset($rules['constants'])) {
            foreach ((array)$rules['constants'] as $constName) {
                if (!defined($constName)) {
                    return false;
                }
            }
        }
        if (isset($rules['functions'])) {
            foreach ((array)$rules['functions'] as $funcName) {
                if (!function_exists($funcName)) {
                    return false;
                }
            }
        }
        return $found;
    }

    public function getPlugin()
    {
        return $this->pluginDetected;
    }
}
