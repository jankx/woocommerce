<?php

namespace Jankx\Ecommerce\Base\Modules;

use Jankx\Ecommerce\Constracts\Renderer;

class ViewedProductsModule implements Renderer
{
    public function render()
    {
    }

    public function __toString()
    {
        return (string)$this->render();
    }
}
