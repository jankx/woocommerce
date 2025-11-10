<?php

namespace Jankx\WooCommerce\Blocks;

use Jankx\Gutenberg\Block;
use WC_Product;

class StockStatusBlock extends Block
{
    protected $blockId = 'jankx/stock-status';

    public function __construct()
    {
        parent::__construct(
            realpath(__DIR__ . '/../../blocks/stock-status')
        );
    }

    /**
     * Render the stock status badge.
     *
     * @param array<string, mixed> $attributes
     * @param string               $content
     * @param \WP_Block|null       $block
     *
     * @return string
     */
    public function render($attributes, $content = '', $block = null)
    {
        $product = $this->resolveProduct($block);

        if (!$product instanceof WC_Product) {
            return $this->renderPreview();
        }

        $status_key = $this->resolveStatus($product);
        $status = $this->mapStatus($status_key, $product);

        return $this->renderMarkup($status);
    }

    protected function resolveProduct($block): ?WC_Product
    {
        if ($block instanceof \WP_Block && !empty($block->context['woocommerce/productId'])) {
            $product = wc_get_product((int) $block->context['woocommerce/productId']);
            if ($product instanceof WC_Product) {
                return $product;
            }
        }

        if ($block instanceof \WP_Block && !empty($block->context['postId'])) {
            $product = wc_get_product((int) $block->context['postId']);
            if ($product instanceof WC_Product) {
                return $product;
            }
        }

        if (isset($GLOBALS['product']) && $GLOBALS['product'] instanceof WC_Product) {
            return $GLOBALS['product'];
        }

        $post_id = get_the_ID();
        if ($post_id) {
            $product = wc_get_product($post_id);
            if ($product instanceof WC_Product) {
                return $product;
            }
        }

        return null;
    }

    protected function resolveStatus(WC_Product $product): string
    {
        $status = $product->get_stock_status();

        if (!$status && $product->managing_stock()) {
            $stock_quantity = $product->get_stock_quantity();
            $status = ($stock_quantity !== null && $stock_quantity > 0) ? 'instock' : 'outofstock';
        }

        /**
         * Allow filtering the resolved stock status slug.
         *
         * @param string     $status  Stock status slug.
         * @param WC_Product $product WooCommerce product instance.
         */
        $status = apply_filters('jankx/woocommerce/stock-status/status', $status, $product);

        return $status ?: 'instock';
    }

    /**
     * @param string     $status
     * @param WC_Product $product
     *
     * @return array{slug: string, label: string, display: string}
     */
    protected function mapStatus(string $status, ?WC_Product $product): array
    {
        $slug = sanitize_key($status ?: 'instock');

        $messages = [
            'instock' => __('In stock', 'jankx'),
            'outofstock' => __('Out of stock', 'jankx'),
            'onbackorder' => __('Available on backorder', 'jankx'),
            'preorder' => __('Pre-order', 'jankx'),
            'coming-soon' => __('Coming soon', 'jankx'),
        ];

        $display = $messages[$slug] ?? $this->humanizeStatus($slug);

        $label = __('Status', 'jankx');

        /**
         * Filter the label displayed before the status badge.
         */
        $label = apply_filters('jankx/woocommerce/stock-status/label', $label, $slug, $product);

        /**
         * Filter the status text displayed inside the badge.
         */
        $display = apply_filters('jankx/woocommerce/stock-status/display', $display, $slug, $product);

        return [
            'slug' => $slug,
            'label' => $label,
            'display' => $display,
        ];
    }

    protected function renderPreview(): string
    {
        $fallback_statuses = [
            'instock',
            'outofstock',
            'onbackorder',
            'preorder',
            'coming-soon',
        ];

        $selection = $fallback_statuses[array_rand($fallback_statuses)];
        $status = $this->mapStatus($selection, null);

        return $this->renderMarkup($status, ['is-preview']);
    }

    /**
     * @param array{slug: string, label: string, display: string} $status
     * @param array<int, string>                                  $extraClasses
     *
     * @return string
     */
    protected function renderMarkup(array $status, array $extraClasses = []): string
    {
        $classes = array_merge(
            ['jankx-stock-status', 'status-' . sanitize_html_class($status['slug'])],
            $extraClasses
        );

        $wrapper_attributes = get_block_wrapper_attributes([
            'class' => implode(' ', array_map('sanitize_html_class', $classes)),
            'data-status' => $status['slug'],
        ]);

        return sprintf(
            '<div %1$s><span class="stock-status__label">%2$s</span><span class="stock-status__badge">%3$s</span></div>',
            $wrapper_attributes,
            esc_html($status['label']),
            esc_html($status['display'])
        );
    }

    protected function humanizeStatus(string $status): string
    {
        $status = str_replace(['-', '_'], ' ', $status);
        $status = ucwords($status);

        return $status;
    }
}

