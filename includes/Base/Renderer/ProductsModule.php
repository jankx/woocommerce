<?php
namespace Jankx\Ecommerce\Base\Renderer;

use Jankx\Ecommerce\Constracts\Renderer;
use Jankx\Ecommerce\Ecommerce;
use Jankx\Ecommerce\EcommerceTemplate;
use Jankx\Ecommerce\Base\GetProductQuery;

class ProductsModule implements Renderer
{
    protected static $supportedFirstTabs;

    protected $categoryIds = array();
    protected $readmore = array();
    protected $args;

    public function __construct($args = array())
    {
        $this->args     = $args;

        if (is_null(static::$supportedFirstTabs)) {
            static::$supportedFirstTabs = apply_filters(
                'jankx_ecommerce_category_tabs_products_first_tabs',
                array(
                    'featured' => __('Featured', 'jankx'),
                    'recents' => __('Recents', 'jankx'),
                )
            );
        }
    }

    public function __toString()
    {
        return (string) $this->render();
    }

    public function setReadMore($url, $text = null)
    {
        if (is_null($text)) {
            $text = __('View all', 'jankx');
        }

        $this->readmore = array(
            'text' => $text,
            'url' => $url
        );
    }

    public function buildFirstTabQuery()
    {
        $productQuery = GetProductQuery::buildQuery($this->args);

        return $productQuery->getWordPressQuery();
    }


    public function render()
    {
        $wp_query   = $this->buildFirstTabQuery();
        $pluginName = jankx_ecommerce()->getShopPlugin()->getName();

        if (is_null($wp_query)) {
            return __('The products not found', 'jankx');
        }

        $data = apply_filters('jankx_ecommerce_products_module_data', array(
            'wp_query' => $wp_query,
        ));

        if ($pluginName === 'woocommerce') {
            wc_set_loop_prop('columns', apply_filters(
                'jankx_tabs_products_columns',
                array_get($this->args, 'items_per_row', 4)
            ));
        }
        // Render the first tab content
        $productList = EcommerceTemplate::render(
            "{$pluginName}/product-list",
            $data,
            'product_list',
            false
        );

        // Render the output
        return EcommerceTemplate::render(
            'base/products/products',
            array(
                'widget_title' => array_get($this->args, 'widget_title'),
                'products' => $productList,
                'readmore' => $this->readmore,
            ),
            null,
            false
        );
    }
}
