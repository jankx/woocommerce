<?php

use Jankx\WooCommerce\WooCommerce;

if (!defined('JANKX_WOOCOMMERCE_FILE_LOADER')) {
    define('JANKX_WOOCOMMERCE_FILE_LOADER', __FILE__);
}

if (!function_exists('jankx_woocommerce')) {
    function jankx_woocommerce()
    {
        return WooCommerce::instance();
    }
}

$GLOBALS['ecommerce'] = jankx_woocommerce();
