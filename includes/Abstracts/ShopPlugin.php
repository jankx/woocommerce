<?php
namespace Jankx\WooCommerce\Abstracts;

use Jankx\WooCommerce\Constracts\ShopPlugin as ShopPluginConstract;

abstract class ShopPlugin implements ShopPluginConstract
{
    public function getContentGenerator()
    {
        return null;
    }
}
