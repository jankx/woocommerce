<?php
namespace Jankx\WooCommerce;

use Jankx\WooCommerce\Hooks\WooCommercePostLayoutHook;

/**
 * WooCommerce Integration
 *
 * Main class cho Jankx WooCommerce integration
 *
 * @package Jankx\WooCommerce
 */
class WooCommerce
{
    /**
     * Initialize the integration
     *
     * @return void
     */
    public static function init()
    {
        // Register Post Layout hooks if WooCommerce is active
        if (class_exists('WooCommerce')) {
            WooCommercePostLayoutHook::init();
        }
    }
}

// Auto-initialize if WordPress is loaded
if (function_exists('add_action')) {
    add_action('init', [WooCommerce::class, 'init'], 20);
}
