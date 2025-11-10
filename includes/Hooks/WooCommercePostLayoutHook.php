<?php

namespace Jankx\WooCommerce\Hooks;

use Jankx\Gutenberg\SmartTabs\SmartTabTriggerRegistry;
use Jankx\WooCommerce\PostLayout\WooCommerceContentGenerator;
use Jankx\WooCommerce\Query\PostTypeLayoutQueryBuilder;
use Jankx\WooCommerce\SmartTabs\ProductReviewsTrigger;
use Jankx\WooCommerce\SmartTabs\ProductAdditionalInfoTrigger;

/**
 * WooCommerce Post Layout Hook
 *
 * Hook để tự động áp dụng WooCommerce Content Generator
 *
 * @package Jankx\WooCommerce\Hooks
 */
class WooCommercePostLayoutHook
{
    /**
     * Initialize hooks
     *
     * @return void
     */
    public static function init()
    {
        // Hook vào Post Layout generator filter
        add_filter('jankx/post-layout/generator', [self::class, 'provideGenerator'], 10, 3);
        
        // Hook vào Post Layout options filter
        add_filter('jankx/post-layout/options', [self::class, 'provideOptions'], 10, 3);
        
        // Register query preset "on-sale" for product post type
        add_filter('jankx/gutenberg/query-options/query-presets', [self::class, 'registerQueryPresets'], 10, 1);
        
        // Register order by options for product post type
        add_filter('jankx/gutenberg/query-options/order-by', [self::class, 'registerOrderByOptions'], 10, 1);
        
        // Hook into query builder filter to handle WooCommerce query presets
        add_filter('jankx/post-layout/query-builder', [self::class, 'buildQuery'], 10, 2);

        // Ensure recently viewed products are tracked even when widget is not active
        add_action('template_redirect', [self::class, 'trackRecentlyViewedProducts'], 25);

        // Register Smart Tab triggers
        add_action('jankx/smart-tabs/register-triggers', [self::class, 'registerSmartTabTriggers']);
    }

    /**
     * Provide WooCommerce generator for product post type
     *
     * @param mixed $generator Current generator
     * @param string $post_type Post type
     * @param array $attributes Block attributes
     * @return mixed
     */
    public static function provideGenerator($generator, $post_type, $attributes)
    {
        // Debug
        $wc_active = class_exists('WooCommerce') || class_exists('WC') || function_exists('WC');
        error_log('WooCommercePostLayoutHook: post_type=' . $post_type . ', woocommerce_active=' . ($wc_active ? 'yes' : 'no'));
        
        // Only for product post type and when WooCommerce is active
        if ($post_type === 'product' && $wc_active) {
            error_log('WooCommercePostLayoutHook: Returning WooCommerceContentGenerator');
            return new WooCommerceContentGenerator();
        }
        
        return $generator;
    }

    /**
     * Provide WooCommerce specific options
     *
     * @param array $options Current options
     * @param string $post_type Post type
     * @param array $attributes Block attributes
     * @return array
     */
    public static function provideOptions($options, $post_type, $attributes)
    {
        // Add WooCommerce specific options for product post type
        if ($post_type === 'product') {
            $options['showPrice'] = $attributes['showPrice'] ?? true;
            $options['showRating'] = $attributes['showRating'] ?? true;
            $options['showAddToCart'] = $attributes['showAddToCart'] ?? true;
            $options['showSaleBadge'] = $attributes['showSaleBadge'] ?? true;
        }
        
        return $options;
    }

    /**
     * Register WooCommerce query presets
     *
     * @param array $presets Current query presets
     * @return array Modified query presets
     */
    public static function registerQueryPresets(array $presets): array
    {
        // Check if WooCommerce is active
        $wc_active = class_exists('WooCommerce') || class_exists('WC') || function_exists('WC');
        
        if (!$wc_active) {
            return $presets;
        }

        // Check if "on-sale" preset already exists
        $has_on_sale = false;
        foreach ($presets as $preset) {
            if (isset($preset['value']) && $preset['value'] === 'on-sale') {
                $has_on_sale = true;
                break;
            }
        }

        // Add "on-sale" preset if not exists
        if (!$has_on_sale) {
            $presets[] = [
                'value' => 'on-sale',
                'label' => __('On Sale Products', 'jankx'),
                'postType' => 'product', // Only available for product post type
                'help' => __('Display products that are currently on sale.', 'jankx'),
            ];
        }

        // Check if "featured" preset already exists
        $has_featured = false;
        foreach ($presets as $preset) {
            if (isset($preset['value']) && $preset['value'] === 'featured') {
                $has_featured = true;
                break;
            }
        }

        // Add "featured" preset if not exists
        if (!$has_featured) {
            $presets[] = [
                'value' => 'featured',
                'label' => __('Featured Products', 'jankx'),
                'postType' => 'product', // Only available for product post type
                'help' => __('Display featured products.', 'jankx'),
            ];
        }

        // Define additional presets to register
        $additional_presets = [
            [
                'value' => 'related-products',
                'label' => __('Related Products', 'jankx'),
                'help' => __('Display related products based on product categories and tags.', 'jankx'),
                'postType' => 'product',
            ],
            [
                'value' => 'recently-viewed',
                'label' => __('Sản phẩm đã xem', 'jankx'),
                'help' => __('Display products the visitor has recently viewed.', 'jankx'),
                'postType' => 'product',
            ],
            [
                'value' => 'best-sellers',
                'label' => __('Best Sellers', 'jankx'),
                'help' => __('Display best selling products.', 'jankx'),
            ],
            [
                'value' => 'top-rated',
                'label' => __('Top Rated Products', 'jankx'),
                'help' => __('Display top rated products.', 'jankx'),
            ],
            [
                'value' => 'upsells',
                'label' => __('Upsells', 'jankx'),
                'help' => __('Display upsell products.', 'jankx'),
            ],
            [
                'value' => 'new-arrivals',
                'label' => __('New Arrivals', 'jankx'),
                'help' => __('Display newly added products.', 'jankx'),
            ],
        ];

        // Register additional presets
        foreach ($additional_presets as $preset_config) {
            $preset_value = $preset_config['value'];
            $has_preset = false;
            
            foreach ($presets as $preset) {
                if (isset($preset['value']) && $preset['value'] === $preset_value) {
                    $has_preset = true;
                    break;
                }
            }

            if (!$has_preset) {
                $presets[] = [
                    'value' => $preset_value,
                    'label' => $preset_config['label'],
                    'postType' => 'product', // Only available for product post type
                    'help' => $preset_config['help'],
                ];
            }
        }

        return $presets;
    }

    /**
     * Register WooCommerce order by options
     *
     * @param array $options Current order by options
     * @return array Modified order by options
     */
    public static function registerOrderByOptions(array $options): array
    {
        // Check if WooCommerce is active
        $wc_active = class_exists('WooCommerce') || class_exists('WC') || function_exists('WC');
        
        if (!$wc_active) {
            return $options;
        }

        // Check if options already have product-specific order by
        $has_sales = false;
        $has_price = false;
        
        foreach ($options as $option) {
            if (isset($option['value'])) {
                if ($option['value'] === 'total_sales') {
                    $has_sales = true;
                }
                if ($option['value'] === '_price') {
                    $has_price = true;
                }
            }
        }

        // Add "Lượt bán" (Sales Count) - using total_sales meta key
        if (!$has_sales) {
            $options[] = [
                'value' => 'total_sales',
                'label' => __('Lượt bán (Sales Count)', 'jankx'),
                'postType' => 'product', // Only available for product post type
                'metaKey' => 'total_sales', // Meta key for sorting
            ];
        }

        // Add "Giá cả" (Price) - using _price meta key
        if (!$has_price) {
            $options[] = [
                'value' => '_price',
                'label' => __('Giá cả (Price)', 'jankx'),
                'postType' => 'product', // Only available for product post type
                'metaKey' => '_price', // Meta key for sorting
            ];
        }

        return $options;
    }

    /**
     * Build query for WooCommerce query presets
     *
     * @param array $attributes Block attributes
     * @param string $queryPreset Query preset name
     * @return array Modified attributes
     */
    public static function buildQuery(array $attributes, string $queryPreset): array
    {
        return PostTypeLayoutQueryBuilder::buildQuery($attributes, $queryPreset);
    }

    /**
     * Register Smart Tab triggers provided by WooCommerce integration.
     *
     * @param SmartTabTriggerRegistry $registry
     * @return void
     */
    public static function registerSmartTabTriggers(SmartTabTriggerRegistry $registry): void
    {
        if (!class_exists('WooCommerce')) {
            return;
        }

        $registry->registerTrigger(new ProductReviewsTrigger());
        $registry->registerTrigger(new ProductAdditionalInfoTrigger());
    }

    /**
     * Track recently viewed products to support custom layouts
     *
     * WooCommerce only tracks when the default widget is active.
     * This replicates the WooCommerce logic so the cookie is always populated.
     *
     * @return void
     */
    public static function trackRecentlyViewedProducts(): void
    {
        if (!function_exists('wc_setcookie')) {
            return;
        }

        if (!is_singular('product')) {
            return;
        }

        global $post;

        if (!$post || !isset($post->ID)) {
            return;
        }

        $viewed_products = self::getRecentlyViewedIdsFromCookie();

        $keys = array_flip($viewed_products);

        if (isset($keys[$post->ID])) {
            unset($viewed_products[$keys[$post->ID]]);
        }

        $viewed_products[] = $post->ID;

        $viewed_products = self::limitRecentlyViewedList($viewed_products);

        wc_setcookie('woocommerce_recently_viewed', implode('|', $viewed_products));

        if (is_user_logged_in()) {
            update_user_meta(get_current_user_id(), self::getUserMetaKey(), $viewed_products);
        }
    }

    /**
     * Retrieve recently viewed IDs from cookie
     *
     * @return array<int>
     */
    protected static function getRecentlyViewedIdsFromCookie(): array
    {
        return !empty($_COOKIE['woocommerce_recently_viewed'])
            ? wp_parse_id_list((array) explode('|', wp_unslash($_COOKIE['woocommerce_recently_viewed'])))
            : [];
    }

    /**
     * Retrieve recently viewed IDs for current user (meta + cookie)
     *
     * @return array<int>
     */
    public static function getRecentlyViewedProductIds(): array
    {
        $cookie_ids = self::getRecentlyViewedIdsFromCookie();

        if (!is_user_logged_in()) {
            return self::limitRecentlyViewedList($cookie_ids);
        }

        $user_ids = get_user_meta(get_current_user_id(), self::getUserMetaKey(), true);
        if (!is_array($user_ids)) {
            $user_ids = [];
        } else {
            $user_ids = array_map('intval', $user_ids);
        }

        if (!empty($cookie_ids)) {
            // Rebuild list to prioritise current session order, falling back to stored meta
            $combined = [];
            foreach ($cookie_ids as $id) {
                $combined[$id] = $id;
            }

            foreach ($user_ids as $id) {
                if (!isset($combined[$id])) {
                    $combined[$id] = $id;
                }
            }

            $viewed_products = array_values($combined);
        } else {
            $viewed_products = $user_ids;
        }

        return self::limitRecentlyViewedList($viewed_products);
    }

    /**
     * Helper to limit recently viewed list length
     *
     * @param array<int> $viewed_products
     * @return array<int>
     */
    protected static function limitRecentlyViewedList(array $viewed_products): array
    {
        $viewed_products = array_values(array_unique(array_map('intval', $viewed_products)));

        /**
         * Filter the maximum number of recently viewed products to store.
         *
         * @param int $max_viewed Default 30 products.
         */
        $max_viewed = (int) apply_filters('jankx/woocommerce/recently_viewed/max_items', 30);
        if ($max_viewed < 1) {
            $max_viewed = 30;
        }

        if (count($viewed_products) > $max_viewed) {
            $viewed_products = array_slice($viewed_products, -1 * $max_viewed);
        }

        return $viewed_products;
    }

    /**
     * Get meta key used to store recently viewed products
     *
     * @return string
     */
    protected static function getUserMetaKey(): string
    {
        /**
         * Filter the user meta key used for storing recently viewed products.
         *
         * @param string $meta_key Default meta key.
         */
        return apply_filters('jankx/woocommerce/recently_viewed/user_meta_key', 'jankx_recently_viewed_products');
    }
}

