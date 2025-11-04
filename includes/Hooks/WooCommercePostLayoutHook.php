<?php

namespace Jankx\WooCommerce\Hooks;

use Jankx\WooCommerce\PostLayout\WooCommerceContentGenerator;

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
}

