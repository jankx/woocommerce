<?php

use Jankx\Ecommerce\Ecommerce;

if (!function_exists('jankx_ecommerce')) {
    function jankx_ecommerce()
    {
        return Ecommerce::instance();
    }
}

$GLOBALS['ecommerce'] = jankx_ecommerce();
