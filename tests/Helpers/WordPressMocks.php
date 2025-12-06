<?php

namespace Jankx\WooCommerce\Tests\Helpers;

/**
 * WordPress Functions Mock
 * 
 * Mock các WordPress functions cần thiết cho testing
 */
class WordPressMocks
{
    public static $options = [];
    public static $transients = [];
    public static $postMeta = [];
    public static $filters = [];
    public static $actions = [];

    /**
     * Initialize storage arrays
     */
    public static function initStorage(): void
    {
        self::$options = [];
        self::$transients = [];
        self::$postMeta = [];
        self::$filters = [];
        self::$actions = [];
    }


    // Options methods
    public static function getOption($option, $default = false)
    {
        return self::$options[$option] ?? $default;
    }

    public static function updateOption($option, $value): bool
    {
        self::$options[$option] = $value;
        return true;
    }

    public static function deleteOption($option): bool
    {
        unset(self::$options[$option]);
        return true;
    }

    // Transients methods
    public static function getTransient($transient)
    {
        return self::$transients[$transient] ?? false;
    }

    public static function setTransient($transient, $value, $expiration = 0): bool
    {
        self::$transients[$transient] = $value;
        return true;
    }

    public static function deleteTransient($transient): bool
    {
        unset(self::$transients[$transient]);
        return true;
    }

    // Hooks methods
    public static function addAction($hook, $callback, $priority = 10, $args = 1): bool
    {
        if (!isset(self::$actions[$hook])) {
            self::$actions[$hook] = [];
        }
        self::$actions[$hook][] = ['callback' => $callback, 'priority' => $priority];
        return true;
    }

    public static function doAction($hook, ...$args): void
    {
        if (isset(self::$actions[$hook])) {
            foreach (self::$actions[$hook] as $action) {
                call_user_func_array($action['callback'], $args);
            }
        }
    }

    public static function addFilter($hook, $callback, $priority = 10, $args = 1): bool
    {
        if (!isset(self::$filters[$hook])) {
            self::$filters[$hook] = [];
        }
        self::$filters[$hook][] = ['callback' => $callback, 'priority' => $priority];
        return true;
    }

    public static function applyFilters($hook, $value, ...$args)
    {
        if (isset(self::$filters[$hook])) {
            foreach (self::$filters[$hook] as $filter) {
                $value = call_user_func_array($filter['callback'], array_merge([$value], $args));
            }
        }
        return $value;
    }

    // Reset methods for tests
    public static function reset(): void
    {
        self::$options = [];
        self::$transients = [];
        self::$postMeta = [];
        self::$filters = [];
        self::$actions = [];
    }

    public static function resetOptions(): void
    {
        self::$options = [];
    }

    public static function resetTransients(): void
    {
        self::$transients = [];
    }

    public static function resetHooks(): void
    {
        self::$filters = [];
        self::$actions = [];
    }

    // Post meta methods
    public static function getPostMeta($post_id, $key = '', $single = false)
    {
        if (empty($key)) {
            return self::$postMeta[$post_id] ?? [];
        }

        $value = self::$postMeta[$post_id][$key] ?? null;

        if ($single) {
            return $value;
        }

        return [$value];
    }

    public static function updatePostMeta($post_id, $meta_key, $meta_value): bool
    {
        if (!isset(self::$postMeta[$post_id])) {
            self::$postMeta[$post_id] = [];
        }
        self::$postMeta[$post_id][$meta_key] = $meta_value;
        return true;
    }

    public static function didAction($hook_name)
    {
        return isset(self::$actions[$hook_name]) ? count(self::$actions[$hook_name]) : 0;
    }

    // Terms methods
    private static $terms = [];
    private static $termRelationships = [];

    public static function getTerm($term_id, $taxonomy = 'product_cat')
    {
        $key = $taxonomy . '_' . $term_id;
        
        if (isset(self::$terms[$key])) {
            return self::$terms[$key];
        }

        // Return mock WP_Term object
        $term = new \WP_Term($term_id, $taxonomy);
        $term->name = 'Test Term ' . $term_id;
        $term->slug = 'test-term-' . $term_id;
        $term->count = rand(5, 50);
        $term->description = '';
        $term->parent = 0;
        
        return $term;
    }

    public static function getTerms($args = [])
    {
        $terms = [];
        $count = $args['number'] ?? 5;
        $taxonomy = $args['taxonomy'] ?? 'product_cat';
        
        for ($i = 1; $i <= $count; $i++) {
            $term = new \WP_Term($i, $taxonomy);
            $term->name = 'Test Term ' . $i;
            $term->slug = 'test-term-' . $i;
            $term->count = rand(5, 50);
            $terms[] = $term;
        }
        
        return $terms;
    }

    public static function getTermChildren($term_id, $taxonomy)
    {
        $key = $taxonomy . '_' . $term_id;
        return self::$termRelationships[$key] ?? [];
    }

    public static function setTermChildren($term_id, $taxonomy, array $children)
    {
        $key = $taxonomy . '_' . $term_id;
        self::$termRelationships[$key] = $children;
    }
}

