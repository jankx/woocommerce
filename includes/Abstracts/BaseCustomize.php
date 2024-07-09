<?php
namespace Jankx\WooCommerce\Abstracts;

use Jankx\WooCommerce\Constracts\CustomizeInterface;

abstract class BaseCustomize implements CustomizeInterface
{
    public function getContentGenerator()
    {
        return null;
    }
}
