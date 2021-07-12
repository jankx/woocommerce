<?php
namespace Jankx\Ecommerce\Abstracts;

use Jankx\Ecommerce\Constracts\ShopPlugin as ShopPluginConstract;

abstract class ShopPlugin implements ShopPluginConstract
{
    public function getContentGenerator()
    {
        return null;
    }
}
