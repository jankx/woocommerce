<?php

namespace Jankx\Woocommerce\Rest;

use WP_Rest_Request;
use Jankx\WooCommerce\GetProductQuery;
use Jankx\WooCommerce\WooCommerce;

class Products extends WP_Rest_Request
{
    public function __construct()
    {
        $this->shopPlugin = WooCommerce::instance()->getShopPlugin();
    }

    public function register()
    {
        register_rest_route(
            'jankx/v1',
            '/ecommerce/get_products',
            array(
                'methods' => array('POST', 'GET'),
                'callback' => array($this, 'getProducts'),
                'permission_callback' => '__return_true'
            )
        );
    }

    protected function parseTabTypeArgs($tab_type, $tab, &$args)
    {
        if ($tab_type === 'category') {
            if ($tab > 0) {
                $args['categories'] = array((int)$tab);
            }
        } elseif ($tab_type === 'special') {
            $args['query_type'] = $tab;
        }
    }

    public function getProducts()
    {
        $args = array();
        if (isset($_REQUEST['tab_type'], $_REQUEST['tab'])) {
            $this->parseTabTypeArgs($_REQUEST['tab_type'], $_REQUEST['tab'], $args);
        }
        $productQuery = GetProductQuery::buildQuery($args);

        $wp_query = $productQuery->getWordPressQuery();
        $products = array();
        $callback = $this->shopPlugin->getProductMethod();
        global $product;

        foreach ($wp_query->posts as $post) {
            $product = $callback($post);
            if (!$product) {
                continue;
            }
            $product_data = $this->shopPlugin->getProductData($product);

            $product_data['thumbnail_image'] = woocommerce_get_product_thumbnail();

            array_push($products, $product_data);
        }

        return array(
            'success' => true,
            'products' => $products,
        );
    }
}
