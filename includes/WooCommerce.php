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
        error_log('WooCommerce::init() called');
        // Register Post Layout hooks if WooCommerce is active
        // Check for WooCommerce main class or WC class
        $wc_exists = class_exists('WooCommerce') || class_exists('WC') || function_exists('WC');
        error_log('WooCommerce detected: ' . ($wc_exists ? 'yes' : 'no'));
        
        if ($wc_exists) {
            error_log('Calling WooCommercePostLayoutHook::init()');
            WooCommercePostLayoutHook::init();
        }
    }
}

// Auto-initialize if WordPress is loaded
if (function_exists('add_action')) {
    add_action('init', [WooCommerce::class, 'init'], 20);
    error_log('WooCommerce integration: Hook registered on init');
} else {
    error_log('WooCommerce integration: add_action not available');
}

