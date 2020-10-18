<?php
namespace Jankx\Ecommerce\Base;

use WC_Product_Simple;
use Jankx\Ecommerce\EcommerceTemplate;

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
            EcommerceTemplate::render('tpl/product', array(), null, false)
        ), false);
    }
}
