<?php

use Jankx\Ecommerce\Ecommerce;
use Jankx\Ecommerce\EcommerceTemplate;

if (!defined('JANKX_ECOMMERCE_FILE_LOADER')) {
    define('JANKX_ECOMMERCE_FILE_LOADER', __FILE__ );
}

if (!function_exists('jankx_ecommerce')) {
    function jankx_ecommerce()
    {
        return Ecommerce::instance();
    }
}

if (!function_exists('jankx_ecommerce_template')) {
    function jankx_ecommerce_template()
    {
        return call_user_func_array(
            array(
                EcommerceTemplate::getTemplateInstance(),
                'render'
            ),
            func_get_args()
        );
    }
}

$GLOBALS['ecommerce'] = jankx_ecommerce();
