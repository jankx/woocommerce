<?php

namespace Jankx\WooCommerce\PostLayout;

use Jankx\Layouts\PostLayout\Contracts\ContentGeneratorInterface;
use WP_Query;

/**
 * WooCommerce Content Generator
 *
 * Generator riêng cho WooCommerce products với đầy đủ chức năng WC
 *
 * @package Jankx\WooCommerce\PostLayout
 */
class WooCommerceContentGenerator implements ContentGeneratorInterface
{
    /**
     * Generator name
     *
     * @var string
     */
    protected $name = 'woocommerce';

    /**
     * Generator title
     *
     * @var string
     */
    protected $title = 'WooCommerce Products';

    /**
     * Supported options
     *
     * @var array
     */
    protected $supportedOptions = [
        'columns',
        'showFeaturedImage',
        'showTitle',
        'showPrice',
        'showRating',
        'showAddToCart',
        'showSaleBadge',
        'postsPerPage',
        'imageSize',
    ];

    /**
     * Check if WooCommerce is active
     *
     * @return bool
     */
    protected function isWooCommerceActive(): bool
    {
        return class_exists('WooCommerce');
    }

    /**
     * {@inheritDoc}
     */
    public function generate(WP_Query $query, array $options = []): string
    {
        if (!$this->isWooCommerceActive()) {
            return '<div class="woocommerce-error">' . __('WooCommerce is not active', 'jankx') . '</div>';
        }

        if (!$query->have_posts()) {
            return '<div class="no-products">' . __('No products found.', 'woocommerce') . '</div>';
        }

        // Save original globals
        global $wp_query, $product;
        $original_query = $wp_query;
        $original_product = isset($product) ? $product : null;

        // Set query to use WooCommerce template
        $wp_query = $query;

        $columns = $options['columns'] ?? 3;

        // Filter WooCommerce columns
        add_filter('loop_shop_columns', function() use ($columns) {
            return (int) $columns;
        }, 999);

        ob_start();
        ?>
        <ul class="products columns-<?php echo esc_attr($columns); ?>">
            <?php
            // Use WooCommerce product loop
            while ($query->have_posts()) {
                $query->the_post();
                global $product;
                
                // Setup product data for WooCommerce
                wc_setup_product_data($GLOBALS['post']);
                
                // Load WooCommerce product template
                wc_get_template_part('content', 'product');
            }
            ?>
        </ul>
        <?php
        
        // Restore original globals
        wp_reset_postdata();
        $wp_query = $original_query;
        $product = $original_product;
        
        // Remove filter
        remove_all_filters('loop_shop_columns');
        
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function generatePreview(array $options = []): array
    {
        return [
            'name' => $this->name,
            'title' => $this->title,
            'type' => 'woocommerce',
            'columns' => $options['columns'] ?? 3,
            'supportedOptions' => $this->supportedOptions,
            'previewItems' => $this->generatePreviewItems($options),
            'woocommerce' => true,
        ];
    }

    /**
     * Generate preview items
     *
     * @param array $options
     * @return array
     */
    protected function generatePreviewItems(array $options = []): array
    {
        $count = min($options['postsPerPage'] ?? 6, 6);
        $items = [];

        for ($i = 0; $i < $count; $i++) {
            $items[] = [
                'id' => $i + 1,
                'title' => sprintf(__('Product %d', 'jankx'), $i + 1),
                'price' => '$' . (29 + $i * 10) . '.99',
                'rating' => 4.0 + ($i % 2) * 0.5,
                'on_sale' => ($i % 3 === 0),
                'thumbnail' => true,
            ];
        }

        return $items;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsOptions(array $options): bool
    {
        if (empty($this->supportedOptions)) {
            return true;
        }

        foreach ($options as $key => $value) {
            if ($value !== false && !in_array($key, $this->supportedOptions, true)) {
                return false;
            }
        }

        return true;
    }
}

