<?php
namespace Jankx\Ecommerce\Integration\Elementor\Widgets;

use WP_Query;
use Elementor\Widget_Base;
use Jankx;
use Jankx\Ecommerce\Base\Modules\ViewedProductsModule;

class ViewedProducts extends Widget_Base
{
    public function get_name()
    {
        return 'viewed_products';
    }

    public function get_title()
    {
        return __('Viewed Products', 'jankx_ecommerce');
    }

    public function get_icon()
    {
        return 'eicon-preview-medium';
    }

    public function get_categories()
    {
        return array('woocommerce', Jankx::templateStylesheet());
    }

    protected function render()
    {
        echo new ViewedProductsModule();
    }
}
