<?php

use Jankx\GlobalConfigs;
use Jankx\WooCommerce\WooCommerceTemplate;

function jankx_woocommerce_template()
{
    return call_user_func_array(
        array(
            WooCommerceTemplate::getEngine(),
            'render'
        ),
        func_get_args()
    );
}

function jankx_woocommerce_single_product_layout()
{
    $layout = apply_filters(
        'jankx/woocommerce/product/detail/layout',
        GlobalConfigs::get('store.detail.layout', 'top-product-info-with-summary-sidebar')
    );
    if ($layout) {
        return $layout;
    }

    return 'default';
}

function jankx_woocommerce_asset_url($path = '')
{
    $abspath = constant('ABSPATH');
    $ecommercePath = dirname(JANKX_WOOCOMMERCE_FILE_LOADER);

    if (PHP_OS === 'WINNT') {
        $abspath = str_replace('\\', '/', $abspath);
        $ecommercePath = str_replace('\\', '/', $ecommercePath);
    }

    $ecommerceDirUrl = str_replace(
        $abspath,
        site_url('/'),
        $ecommercePath
    );
    return sprintf('%s/assets/%s', $ecommerceDirUrl, $path);
}

function jankx_woocommerce_get_recently_view_products() {
    $viewed_products = ! empty( $_COOKIE['woocommerce_recently_viewed'] ) ? (array) explode( '|', wp_unslash( $_COOKIE['woocommerce_recently_viewed'] ) ) : array(); // @codingStandardsIgnoreLine
    $viewed_products = array_reverse( array_filter( array_map( 'absint', $viewed_products ) ) );

    return apply_filters('jankx_woocommerce_recently_viewed_products', $viewed_products);
}
