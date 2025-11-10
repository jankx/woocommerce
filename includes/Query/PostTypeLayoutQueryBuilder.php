<?php

namespace Jankx\WooCommerce\Query;

use Jankx\WooCommerce\Hooks\WooCommercePostLayoutHook;

/**
 * Post Type Layout Query Builder for WooCommerce
 *
 * Handles query building for WooCommerce-specific query presets
 *
 * @package Jankx\WooCommerce\Query
 */
class PostTypeLayoutQueryBuilder
{
    /**
     * Build query attributes based on query preset
     *
     * @param array $attributes Block attributes
     * @param string $queryPreset Query preset name
     * @return array Modified attributes
     */
    public static function buildQuery(array $attributes, string $queryPreset): array
    {
        $post_type = $attributes['postType'] ?? 'post';
        
        // Only apply for product post type
        if ($post_type !== 'product') {
            return $attributes;
        }

        // Check if WooCommerce is active
        if (!class_exists('WooCommerce') && !class_exists('WC') && !function_exists('WC')) {
            return $attributes;
        }

        switch ($queryPreset) {
            case 'on-sale':
                return self::buildOnSaleQuery($attributes);
            case 'featured':
                return self::buildFeaturedQuery($attributes);
            case 'recently-viewed':
                return self::buildRecentlyViewedQuery($attributes);
            case 'related-products':
                return self::buildRelatedProductsQuery($attributes);
            case 'best-sellers':
                return self::buildBestSellersQuery($attributes);
            case 'top-rated':
                return self::buildTopRatedQuery($attributes);
            case 'upsells':
                return self::buildUpsellsQuery($attributes);
            case 'new-arrivals':
                return self::buildNewArrivalsQuery($attributes);
            default:
                return $attributes;
        }
    }

    /**
     * Build on-sale products query
     *
     * @param array $attributes Block attributes
     * @return array Modified attributes with meta_query for on-sale products
     */
    protected static function buildOnSaleQuery(array $attributes): array
    {
        $current_time = current_time('timestamp');
        $meta_query = $attributes['metaQuery'] ?? [];

        // Build meta query for on-sale products
        // A product is on sale if:
        // 1. _sale_price exists and is not empty
        // 2. _sale_price_dates_from <= current time (if exists) or doesn't exist
        // 3. _sale_price_dates_to >= current time (if exists) or doesn't exist
        
        $sale_meta_query = [
            'relation' => 'AND',
            // _sale_price must exist and not be empty
            [
                'key' => '_sale_price',
                'value' => '',
                'compare' => '!=',
            ],
            // Check sale price dates from (if exists, must be <= current time, or doesn't exist)
            [
                'relation' => 'OR',
                [
                    'key' => '_sale_price_dates_from',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key' => '_sale_price_dates_from',
                    'value' => '',
                    'compare' => '=',
                ],
                [
                    'key' => '_sale_price_dates_from',
                    'value' => $current_time,
                    'compare' => '<=',
                    'type' => 'NUMERIC',
                ],
            ],
            // Check sale price dates to (if exists, must be >= current time, or doesn't exist)
            [
                'relation' => 'OR',
                [
                    'key' => '_sale_price_dates_to',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key' => '_sale_price_dates_to',
                    'value' => '',
                    'compare' => '=',
                ],
                [
                    'key' => '_sale_price_dates_to',
                    'value' => $current_time,
                    'compare' => '>=',
                    'type' => 'NUMERIC',
                ],
            ],
        ];

        // Add to existing meta query
        if (!empty($meta_query)) {
            $attributes['metaQuery'] = [
                'relation' => 'AND',
                $sale_meta_query,
                $meta_query,
            ];
        } else {
            $attributes['metaQuery'] = $sale_meta_query;
        }

        return $attributes;
    }

    /**
     * Build featured products query
     *
     * @param array $attributes Block attributes
     * @return array Modified attributes with meta_query for featured products
     */
    protected static function buildFeaturedQuery(array $attributes): array
    {
        $meta_query = $attributes['metaQuery'] ?? [];

        // Featured products have _featured meta key set to 'yes'
        $featured_meta_query = [
            'key' => '_featured',
            'value' => 'yes',
            'compare' => '=',
        ];

        // Add to existing meta query
        if (!empty($meta_query)) {
            if (isset($meta_query['relation'])) {
                // If relation exists, preserve it and add new query
                $meta_query[] = $featured_meta_query;
                $attributes['metaQuery'] = $meta_query;
            } else {
                // If no relation, wrap existing queries with AND relation
                $attributes['metaQuery'] = [
                    'relation' => 'AND',
                    $meta_query,
                    $featured_meta_query,
                ];
            }
        } else {
            $attributes['metaQuery'] = [$featured_meta_query];
        }

        return $attributes;
    }

    /**
     * Build related products query
     *
     * @param array $attributes Block attributes
     * @return array Modified attributes with tax_query for related products
     */
    protected static function buildRelatedProductsQuery(array $attributes): array
    {
        if (!is_singular('product')) {
            return $attributes;
        }

        $current_post = get_queried_object();
        if (!$current_post || !isset($current_post->ID)) {
            return $attributes;
        }

        $attributes['postNotIn'] = array_merge(
            $attributes['postNotIn'] ?? [],
            [$current_post->ID]
        );

        // Get product categories and tags
        $taxonomies = ['product_cat', 'product_tag'];
        $tax_queries = [];

        foreach ($taxonomies as $taxonomy) {
            $terms = get_the_terms($current_post->ID, $taxonomy);

            if ($terms && !is_wp_error($terms)) {
                $term_ids = array_map(function ($term) {
                    return $term->term_id;
                }, $terms);

                if (!empty($term_ids)) {
                    $tax_queries[] = [
                        'taxonomy' => $taxonomy,
                        'field' => 'term_id',
                        'terms' => $term_ids,
                        'operator' => 'IN',
                    ];
                }
            }
        }

        if (!empty($tax_queries)) {
            $existing_tax_query = $attributes['taxQuery'] ?? [];
            
            // If multiple tax queries, wrap them in OR relation
            if (count($tax_queries) > 1) {
                $combined_tax_query = [
                    'relation' => 'OR',
                ];
                foreach ($tax_queries as $tax_query) {
                    $combined_tax_query[] = $tax_query;
                }
                $tax_queries = $combined_tax_query;
            } else {
                $tax_queries = $tax_queries[0];
            }
            
            if (!empty($existing_tax_query)) {
                $attributes['taxQuery'] = [
                    'relation' => 'AND',
                    $existing_tax_query,
                    $tax_queries,
                ];
            } else {
                $attributes['taxQuery'] = $tax_queries;
            }
        }

        return $attributes;
    }

    /**
     * Build best sellers query
     *
     * @param array $attributes Block attributes
     * @return array Modified attributes with orderby for best sellers
     */
    protected static function buildBestSellersQuery(array $attributes): array
    {
        // Best sellers are sorted by total_sales in descending order
        $attributes['orderBy'] = 'meta_value_num';
        $attributes['metaKey'] = 'total_sales';
        $attributes['order'] = 'DESC';

        // Only include products that have been sold at least once
        $meta_query = $attributes['metaQuery'] ?? [];
        $sales_meta_query = [
            'key' => 'total_sales',
            'value' => '0',
            'compare' => '>',
            'type' => 'NUMERIC',
        ];

        if (!empty($meta_query)) {
            if (isset($meta_query['relation'])) {
                // If relation exists, preserve it and add new query
                $meta_query[] = $sales_meta_query;
                $attributes['metaQuery'] = $meta_query;
            } else {
                // If no relation, wrap existing queries with AND relation
                $attributes['metaQuery'] = [
                    'relation' => 'AND',
                    $meta_query,
                    $sales_meta_query,
                ];
            }
        } else {
            $attributes['metaQuery'] = [$sales_meta_query];
        }

        return $attributes;
    }

    /**
     * Build top rated products query
     *
     * @param array $attributes Block attributes
     * @return array Modified attributes with orderby for top rated products
     */
    protected static function buildTopRatedQuery(array $attributes): array
    {
        // Top rated products are sorted by average rating in descending order
        $attributes['orderBy'] = 'meta_value_num';
        $attributes['metaKey'] = '_wc_average_rating';
        $attributes['order'] = 'DESC';

        // Only include products that have been rated
        $meta_query = $attributes['metaQuery'] ?? [];
        $rating_meta_query = [
            'key' => '_wc_average_rating',
            'value' => '0',
            'compare' => '>',
            'type' => 'NUMERIC',
        ];

        if (!empty($meta_query)) {
            if (isset($meta_query['relation'])) {
                // If relation exists, preserve it and add new query
                $meta_query[] = $rating_meta_query;
                $attributes['metaQuery'] = $meta_query;
            } else {
                // If no relation, wrap existing queries with AND relation
                $attributes['metaQuery'] = [
                    'relation' => 'AND',
                    $meta_query,
                    $rating_meta_query,
                ];
            }
        } else {
            $attributes['metaQuery'] = [$rating_meta_query];
        }

        return $attributes;
    }

    /**
     * Build upsells query
     *
     * @param array $attributes Block attributes
     * @return array Modified attributes with post__in for upsells
     */
    protected static function buildUpsellsQuery(array $attributes): array
    {
        if (!is_singular('product')) {
            return $attributes;
        }

        $current_post = get_queried_object();
        if (!$current_post || !isset($current_post->ID)) {
            return $attributes;
        }

        // Get upsell product IDs
        $upsell_ids = get_post_meta($current_post->ID, '_upsell_ids', true);

        if (empty($upsell_ids) || !is_array($upsell_ids)) {
            // If no upsells, set post__in to empty array to return no results
            $attributes['postIn'] = [];
        } else {
            $attributes['postIn'] = array_map('intval', $upsell_ids);
        }

        return $attributes;
    }

    /**
     * Build new arrivals query
     *
     * @param array $attributes Block attributes
     * @return array Modified attributes with orderby for new arrivals
     */
    protected static function buildNewArrivalsQuery(array $attributes): array
    {
        // New arrivals are sorted by date in descending order (newest first)
        $attributes['orderBy'] = 'date';
        $attributes['order'] = 'DESC';

        return $attributes;
    }

    /**
     * Build recently viewed products query
     *
     * @param array $attributes Block attributes
     * @return array Modified attributes with post__in for recently viewed products
     */
    protected static function buildRecentlyViewedQuery(array $attributes): array
    {
        if (!function_exists('wc_get_product')) {
            return $attributes;
        }

        // WooCommerce stores viewed product IDs in cookie
        $viewed_products = WooCommercePostLayoutHook::getRecentlyViewedProductIds();

        if (empty($viewed_products)) {
            // No viewed products -> return empty result
            $attributes['postIn'] = [0];
            return $attributes;
        }

        $attributes['postIn'] = $viewed_products;
        $attributes['orderBy'] = 'post__in';
        $attributes['order'] = 'ASC';

        return $attributes;
    }
}

