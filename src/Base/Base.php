<?php
namespace Jankx\Ecommerce\Base;

abstract class Base
{
    public function __toString()
    {
        return (string) $this->render();
    }

    abstract public function render();
}
