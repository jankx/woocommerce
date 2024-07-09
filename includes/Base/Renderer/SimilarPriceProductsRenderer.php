<?php

namespace Jankx\WooCommerce\Base\Renderer;

use WP_Query;
use Jankx\WooCommerce\Constracts\Renderer;
use Jankx\WooCommerce\WooCommerce;
use Jankx\WooCommerce\WooCommerceTemplate;
use Jankx\WooCommerce\Customize;
use Jankx\Widget\Renderers\Base as RendererBase;

class SimilarPriceProductsRenderer extends RendererBase
{
    public function render()
    {
        $pluginName = WooCommerce::instance()->getShopPlugin()->getName();
        if ($pluginName === Customize::PLUGIN_NAME) {
            return $this->getWooCommerceViewedProducts();
        }
    }

    public function __toString()
    {
        return (string)$this->render();
    }

    public function getWooCommerceViewedProducts()
    {
        $viewed_products = jankx_woocommerce_get_recently_view_products();
        if (empty($viewed_products)) {
            return;
        }

        $number = 4;
        $query_args = array(
            'posts_per_page' => $number,
            'no_found_rows'  => 1,
            'post_status'    => 'publish',
            'post_type'      => 'product',
            'post__in'       => $viewed_products,
            'orderby'        => 'post__in',
        );
        if ('yes' === get_option('woocommerce_hide_out_of_stock_items')) {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_visibility',
                    'field'    => 'name',
                    'terms'    => 'outofstock',
                    'operator' => 'NOT IN',
                ),
            ); // WPCS: slow query ok.
        }

        $recently_views = new WP_Query(apply_filters(
            'woocommerce_similar_price_products_widget_query_args',
            $query_args
        ));

        return WooCommerceTemplate::render('base/products/similar-price-products', array(
            'wp_query' => $recently_views
        ), null, false);
    }
}
