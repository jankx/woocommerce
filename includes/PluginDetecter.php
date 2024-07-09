<?php
namespace Jankx\WooCommerce;

use Jankx\WooCommerce\Customize;

class PluginDetecter
{
    protected $detectPluginRules;
    protected $detectedPlugin;

    public function __construct()
    {
        $defaultRules = array(
            Customize::PLUGIN_NAME => array(
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
        $this->detectPluginRules = apply_filters('jankx_woocommerce_detect_plugin_rules', $defaultRules);
    }

    public function getECommercePlugin()
    {
        foreach ($this->detectPluginRules as $pluginName => $rules) {
            if ($this->parseRules($rules)) {
                $this->detectedPlugin = trim($pluginName);
                return $this->detectedPlugin;
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
        return $this->detectedPlugin;
    }
}
