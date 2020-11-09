<?php
namespace Jankx\Ecommerce\Widgets;

class Manager
{
    public static function register()
    {
        register_widget(ProductAttributesWidget::class);
    }
}
