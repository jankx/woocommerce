<?php
namespace Jankx\WooCommerce;

use WC_Product_Simple;
use Jankx\WooCommerce\WooCommerceTemplate;

class TemplateManager
{
    protected static $productTplCreated = false;

    public static function createProductJsTemplate()
    {
        if (static::$productTplCreated) {
            return;
        }

        static::$productTplCreated = true;
        init_script(sprintf(
            '<script type="text/x-tmpl" id="jankx-ecommerce-product-tpl">%s</script>',
            WooCommerceTemplate::render('tpl/product', array(), null, false)
        ), false);
    }
}
