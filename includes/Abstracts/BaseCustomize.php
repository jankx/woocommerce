<?php

namespace Jankx\WooCommerce\Abstracts;

if (!defined('ABSPATH')) {
    exit('Cheating huh?');
}

use Jankx\WooCommerce\Constracts\CustomizeInterface;

abstract class BaseCustomize implements CustomizeInterface
{
    public function getContentGenerator()
    {
        return null;
    }
}
