<?php

use Jankx\Ecommerce\Manager;

if (!function_exists('jankx_ecommerce')) {
    function jankx_ecommerce() {
        return Manager::instance();
    }
}

$GLOBALS['ecommerce'] = jankx_ecommerce();