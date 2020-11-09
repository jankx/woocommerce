<?php
namespace Jankx\Ecommerce\Widgets;

use WP_Widget;

class ProductAttributesWidget extends WP_Widget {
    public function __construct() {
        parent::__construct('jankx-product-attributes', __('Jankx Product Attributes', 'jankx_ecommerce'), array(
            'classname' => 'jankx-product-attributes',
            'description' => __('Show product attributes in product single page', 'jankx_ecomerce'),
        ));
    }

    public function widget($args, $instance) {
    }
}
