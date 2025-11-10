<?php
namespace Jankx\WooCommerce;

use Jankx\WooCommerce\Hooks\WooCommercePostLayoutHook;

require_once __DIR__ . '/SmartTabs/ProductReviewsTrigger.php';

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
            
            // Ensure store notices container exists for WooCommerce blocks
            add_action('wp_footer', [self::class, 'ensure_store_notices_container'], 5);
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

    /**
     * Ensure store notices container exists in DOM
     *
     * WooCommerce blocks need this container to display notices
     * when adding products to cart
     *
     * @return void
     */
    public static function ensure_store_notices_container(): void
    {
        if (!self::is_woocommerce_data_page()) {
            return;
        }

        // Check if store notices container already exists
        if (did_action('woocommerce_before_single_product') || 
            did_action('woocommerce_before_shop_loop')) {
            return;
        }

        // Add store notices container if not exists
        // WooCommerce blocks need this for displaying add-to-cart notices
        ?>
        <div class="wc-block-components-notices" data-wp-interactive="woocommerce/store-notices" data-wp-context='{"notices":[]}'></div>
        <script>
        // Fix for store-notices.js error: ensure notices is always an array
        // This prevents "can't access property 'find', e is undefined" error
        (function() {
            'use strict';
            
            // Patch immediately - don't wait for DOMContentLoaded
            function patchStoreNotices() {
                try {
                    // Ensure all store-notices containers have valid context
                    const containers = document.querySelectorAll('.wc-block-components-notices');
                    containers.forEach(function(container) {
                        const contextAttr = container.getAttribute('data-wp-context');
                        if (!contextAttr) {
                            container.setAttribute('data-wp-context', JSON.stringify({ notices: [] }));
                        } else {
                            try {
                                const context = JSON.parse(contextAttr.replace(/'/g, '"'));
                                if (!context || !context.notices || !Array.isArray(context.notices)) {
                                    container.setAttribute('data-wp-context', JSON.stringify({ notices: [] }));
                                }
                            } catch (e) {
                                container.setAttribute('data-wp-context', JSON.stringify({ notices: [] }));
                            }
                        }
                    });
                    
                    // Patch Interactivity API to ensure context is always initialized
                    if (typeof window.wp !== 'undefined' && window.wp.interactivity) {
                        const originalGetContext = window.wp.interactivity.getContext;
                        if (originalGetContext) {
                            window.wp.interactivity.getContext = function(ref) {
                                const context = originalGetContext.apply(this, arguments);
                                if (ref && ref.closest && ref.closest('[data-wp-interactive*="store-notices"]')) {
                                    if (!context || !context.notices) {
                                        return Object.assign({ notices: [] }, context || {});
                                    }
                                    if (!Array.isArray(context.notices)) {
                                        context.notices = [];
                                    }
                                }
                                return context;
                            };
                        }
                    }
                    
                    // Intercept addNotice calls to prevent errors
                    if (typeof window.wp !== 'undefined' && window.wp.store && window.wp.store.dispatch) {
                        const originalDispatch = window.wp.store.dispatch;
                        window.wp.store.dispatch = function(storeName, actionName, ...args) {
                            if (storeName === 'woocommerce/store-notices' && actionName === 'addNotice') {
                                try {
                                    // Ensure context exists before calling addNotice
                                    const containers = document.querySelectorAll('[data-wp-interactive*="store-notices"]');
                                    containers.forEach(function(container) {
                                        let contextAttr = container.getAttribute('data-wp-context');
                                        if (!contextAttr) {
                                            contextAttr = JSON.stringify({ notices: [] });
                                            container.setAttribute('data-wp-context', contextAttr);
                                        } else {
                                            try {
                                                const context = JSON.parse(contextAttr.replace(/'/g, '"'));
                                                if (!context || !context.notices || !Array.isArray(context.notices)) {
                                                    container.setAttribute('data-wp-context', JSON.stringify({ notices: [] }));
                                                }
                                            } catch (e) {
                                                container.setAttribute('data-wp-context', JSON.stringify({ notices: [] }));
                                            }
                                        }
                                    });
                                    
                                    return originalDispatch.apply(this, arguments);
                                } catch (e) {
                                    console.warn('WooCommerce store notices dispatch error:', e);
                                    return Promise.resolve();
                                }
                            }
                            return originalDispatch.apply(this, arguments);
                        };
                    }
                } catch (e) {
                    console.warn('WooCommerce store notices patch error:', e);
                }
            }
            
            // Run immediately and also on DOMContentLoaded
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', patchStoreNotices);
            } else {
                patchStoreNotices();
            }
            
            // Also run after a short delay to catch dynamically added elements
            setTimeout(patchStoreNotices, 100);
            setTimeout(patchStoreNotices, 500);
        })();
        </script>
        <?php
    }
}

// Auto-initialize if WordPress is loaded
if (function_exists('add_action')) {
    add_action('init', [WooCommerce::class, 'init'], 20);
    error_log('WooCommerce integration: Hook registered on init');
} else {
    error_log('WooCommerce integration: add_action not available');
}

