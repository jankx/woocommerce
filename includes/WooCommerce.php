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
            
            // Enqueue WooCommerce product template style
            add_action('wp_enqueue_scripts', [self::class, 'enqueue_product_template_style'], 20);
        }
    }

    /**
     * Check if current page is a WooCommerce data page
     *
     * @return bool
     */
    public static function is_woocommerce_data_page()
    {
        if (!function_exists('is_woocommerce')) {
            return false;
        }

        // Check if is product, product archive, or product taxonomy pages
        if (is_product() || is_shop() || is_product_category() || is_product_tag()) {
            return true;
        }

        // Check for product_brand taxonomy (if exists)
        if (is_tax('product_brand')) {
            return true;
        }

        // Check if current post type is product
        if (is_singular('product')) {
            return true;
        }

        // Check if current taxonomy is product_cat or product_tag
        $queried_object = get_queried_object();
        if ($queried_object instanceof \WP_Term) {
            $taxonomy = $queried_object->taxonomy;
            if (in_array($taxonomy, ['product_cat', 'product_tag', 'product_brand'])) {
                return true;
            }
        }

        // Check if current post type archive is product
        if (is_post_type_archive('product')) {
            return true;
        }

        return false;
    }

    /**
     * Enqueue WooCommerce product template style
     *
     * @return void
     */
    public static function enqueue_product_template_style()
    {
        if (!self::is_woocommerce_data_page()) {
            return;
        }

        $style_url = plugins_url(
            'assets/client/blocks/woocommerce/product-template-style.css',
            WC_PLUGIN_FILE
        );

        $version = defined('WC_VERSION') ? WC_VERSION : '6.8.3';

        wp_enqueue_style(
            'woocommerce-product-template-style',
            $style_url,
            [],
            $version,
            'all'
        );
    }
}

// Auto-initialize if WordPress is loaded
if (function_exists('add_action')) {
    add_action('init', [WooCommerce::class, 'init'], 20);
    error_log('WooCommerce integration: Hook registered on init');
} else {
    error_log('WooCommerce integration: add_action not available');
}

