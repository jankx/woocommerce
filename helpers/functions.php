<?php
use Jankx\Ecommerce\EcommerceTemplate;

function jankx_ecommerce_template()
{
    return call_user_func_array(
        array(
            EcommerceTemplate::getTemplateInstance(),
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
