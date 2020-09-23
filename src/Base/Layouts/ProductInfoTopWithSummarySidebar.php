<?php
namespace Jankx\Ecommerce\Base\Layouts;

use Jankx\Ecommerce\EcommerceTemplate;

class ProductInfoTopWithSummarySidebar
{
    const LAYOUT_NAME = 'product-info-top-with-summary-sidebar';

    public function __construct() {
        add_action('jankx_template_after_header', array($this, 'move_summary_to_top'), 18);
        add_action('jankx_ecommerce_woocommerce_content_single_product', array($this, 'change_single_product_layout'));
    }

    public function move_summary_to_top() {

    }

    public function change_single_product_layout() {
    }
}
