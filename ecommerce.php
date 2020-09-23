<?php

use Jankx\Ecommerce\Ecommerce;

if (!defined('JANKX_ECOMMERCE_FILE_LOADER')) {
    define('JANKX_ECOMMERCE_FILE_LOADER', __FILE__ );
}

if (!function_exists('jankx_ecommerce')) {
    function jankx_ecommerce()
    {
        return Ecommerce::instance();
    }
}

$GLOBALS['ecommerce'] = jankx_ecommerce();
