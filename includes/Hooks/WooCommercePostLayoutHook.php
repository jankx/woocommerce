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
        die('zo');
        // Only for product post type and when WooCommerce is active
        if ($post_type === 'product' && class_exists('WooCommerce')) {
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
}

