<?php

/**
 * WooCommerceData trait
 *
 * This trait copy from WooCommerce v4.5.2
 */

namespace Jankx\WooCommerce\Traits;

trait WooCommerceData
{
    /**
     * Copy method from WC_REST_Products_V2_Controller::get_product_data method
     *
     * @param  WC_Product $product The WooCommerce product
     */
    public function getProductData($product)
    {
        $context = 'view';

        $data = array(
            'id'                => $product->get_id(),
            'post_class'        => implode(' ', get_post_class('', $product->get_id())),
            'name'              => $product->get_name($context),
            'slug'              => $product->get_slug($context),
            'permalink'         => $product->get_permalink(),
            'type'              => $product->get_type(),
            'status'            => $product->get_status($context),
            'featured'          => $product->is_featured(),
            'sku'               => $product->get_sku($context),
            'price'             => $product->get_price($context),
            'regular_price'     => $product->get_regular_price($context),
            'sale_price'        => $product->get_sale_price($context) ? $product->get_sale_price($context) : '',
            'date_on_sale_from' => wc_rest_prepare_date_response($product->get_date_on_sale_from($context), false),
            'date_on_sale_to'   => wc_rest_prepare_date_response($product->get_date_on_sale_to($context), false),
            'price_html'        => $product->get_price_html(),
            'on_sale'           => $product->is_on_sale($context),
            'purchasable'       => $product->is_purchasable(),
            'total_sales'       => $product->get_total_sales($context),
            'virtual'           => $product->is_virtual(),
            'external_url'      => $product->is_type('external') ? $product->get_product_url($context) : '',
            'button_text'       => $product->is_type('external') ? $product->get_button_text($context) : '',
            'reviews_allowed'   => $product->get_reviews_allowed($context),
            'average_rating'    => 'view' === $context ? wc_format_decimal($product->get_average_rating(), 2) : $product->get_average_rating($context),
            'rating_count'      => $product->get_rating_count(),
            'parent_id'         => $product->get_parent_id($context),
        );

        return apply_filters('jankx_woocommerce_woocommere_get_product_data', $data, $product);
    }
}
