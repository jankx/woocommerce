<?php
use Jankx\Ecommerce\EcommerceTemplate;

function jankx_ecommerce_template()
{
    return call_user_func_array(
        array(
            EcommerceTemplate::getEngine(),
            'render'
        ),
        func_get_args()
    );
}

function jankx_ecommerce_single_product_layout()
{
    $layout = apply_filters('jankx_ecommerce_single_product_layout', null);
    if ($layout) {
        return $layout;
    }

    return 'default';
}

function jankx_ecommerce_asset_url($path = '')
{
    $abspath = constant('ABSPATH');
    $ecommercePath = dirname(JANKX_ECOMMERCE_FILE_LOADER);

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

function jankx_ecommerce_get_recently_view_products() {
    $viewed_products = ! empty( $_COOKIE['woocommerce_recently_viewed'] ) ? (array) explode( '|', wp_unslash( $_COOKIE['woocommerce_recently_viewed'] ) ) : array(); // @codingStandardsIgnoreLine
    $viewed_products = array_reverse( array_filter( array_map( 'absint', $viewed_products ) ) );

    return apply_filters('jankx_ecommerce_recently_viewed_products', $viewed_products);
}
