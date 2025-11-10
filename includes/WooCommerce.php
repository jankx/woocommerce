<?php
namespace Jankx\WooCommerce;

use Jankx\WooCommerce\Blocks\BuyNowButtonBlock;
use Jankx\WooCommerce\Blocks\DiscountPercentsBlock;
use Jankx\WooCommerce\Blocks\StockStatusBlock;
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
        if (!self::isWooCommerceActive()) {
            return;
        }

        if (!class_exists(WooCommercePostLayoutHook::class)) {
            return;
        }

        WooCommercePostLayoutHook::init();

        // Allow the WooCommerce package to register its own Gutenberg blocks.
        add_action(
            'jankx/gutenberg/register-blocks',
            [self::class, 'registerBlocks'],
            10,
            2
        );

        add_filter(
            'woocommerce_add_to_cart_redirect',
            [self::class, 'maybeRedirectBuyNow'],
            20,
            2
        );

        // Enqueue WooCommerce product template style
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_product_template_style'], 20);

        // Ensure store notices container exists for WooCommerce blocks
        add_action('wp_footer', [self::class, 'ensure_store_notices_container'], 5);
    }

    /**
     * Determine if WooCommerce is active.
     *
     * @return bool
     */
    protected static function isWooCommerceActive(): bool
    {
        return class_exists('WooCommerce') || class_exists('WC') || function_exists('WC');
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

        $version = defined('WC_VERSION') ? constant('WC_VERSION') : '6.8.3';

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

    /**
     * Register Gutenberg blocks exposed by the WooCommerce integration.
     *
     * @param \Jankx\Gutenberg\GutenbergRepository $repository
     * @param \Jankx\Foundation\Application|null   $app
     *
     * @return void
     */
    public static function registerBlocks($repository, $app = null): void
    {
        if (!is_object($repository)) {
            return;
        }

        $blockClasses = array_filter([
            class_exists(DiscountPercentsBlock::class) ? DiscountPercentsBlock::class : null,
            class_exists(BuyNowButtonBlock::class) ? BuyNowButtonBlock::class : null,
            class_exists(StockStatusBlock::class) ? StockStatusBlock::class : null,
        ]);

        foreach ($blockClasses as $blockClass) {
            if (method_exists($repository, 'hasBlock') && $repository->hasBlock($blockClass)) {
                continue;
            }
            $repository->registerBlock($blockClass);
        }
    }

    /**
     * Redirect to checkout when Buy Now requests are triggered.
     *
     * @param string $url
     * @param int|\WC_Product $product
     *
     * @return string
     */
    public static function maybeRedirectBuyNow($url, $product)
    {
        if (empty($_REQUEST['wc_buy_now'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return $url;
        }

        $checkout_url = wc_get_checkout_url();

        return $checkout_url ?: $url;
    }
}

// Auto-initialize if WordPress is loaded
if (function_exists('add_action')) {
    add_action('init', [WooCommerce::class, 'init'], 5);
    error_log('WooCommerce integration: Hook registered on init');
} else {
    error_log('WooCommerce integration: add_action not available');
}

