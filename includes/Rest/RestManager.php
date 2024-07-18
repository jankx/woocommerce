<?php

namespace Jankx\WooCommerce\Rest;

class RestManager
{
    protected static $instance;

    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    private function __construct()
    {
        add_action('rest_api_init', array($this, 'registerProductsEndpoints'));
    }

    public function registerProductsEndpoints()
    {
        $restProducts = new Products();
        $restProducts->register();
    }
}
