<?php

namespace Jankx\WooCommerce\Blocks;

use Jankx\Gutenberg\Block;
use WC_Product;

class DiscountPercentsBlock extends Block
{
    /**
     * Block identifier registered in block.json.
     *
     * @var string
     */
    protected $blockId = 'jankx/discount-percents';

    public function __construct()
    {
        parent::__construct(
            realpath(__DIR__ . '/../../blocks/discount-percents')
        );
    }

    /**
     * Render the discount percent badge.
     *
     * @param array<string, mixed> $attributes
     * @param string $content
     * @param \WP_Block|null $block
     *
     * @return string
     */
    public function render($attributes, $content = '', $block = null)
    {
        if (!function_exists('wc_get_product')) {
            return '';
        }

        $product = $this->resolveProduct($block);

        if (!$product instanceof WC_Product) {
            return $this->renderPlaceholder($attributes);
        }

        $discount = $this->calculateDiscount($product);

        if ($discount <= 0 && empty($attributes['displayZero'])) {
            return '';
        }

        $value = max(0, $discount);

        return $this->renderBadge((int) $value, $attributes);
    }

    /**
     * Attempt to resolve the current product context.
     *
     * @param \WP_Block|null $block
     *
     * @return WC_Product|null
     */
    protected function resolveProduct($block): ?WC_Product
    {
        if ($block instanceof \WP_Block && !empty($block->context['postId'])) {
            $product = wc_get_product((int) $block->context['postId']);
            if ($product instanceof WC_Product) {
                return $product;
            }
        }

        if (isset($GLOBALS['product']) && $GLOBALS['product'] instanceof WC_Product) {
            return $GLOBALS['product'];
        }

        $postId = get_the_ID();
        if ($postId) {
            $product = wc_get_product($postId);
            if ($product instanceof WC_Product) {
                return $product;
            }
        }

        return null;
    }

    /**
     * Calculate the discount percentage for a product.
     *
     * @param WC_Product $product
     *
     * @return int
     */
    protected function calculateDiscount(WC_Product $product): int
    {
        $regular = $this->getRegularPrice($product);
        $sale = $this->getSalePrice($product);

        if ($regular <= 0.0 || $sale <= 0.0 || $sale >= $regular) {
            return 0;
        }

        $percentage = (($regular - $sale) / $regular) * 100;

        return (int) round($percentage);
    }

    /**
     * Retrieve regular price for simple and variable products.
     *
     * @param WC_Product $product
     *
     * @return float
     */
    protected function getRegularPrice(WC_Product $product): float
    {
        if ($product->is_type('variable')) {
            return (float) $product->get_variation_regular_price('min', true);
        }

        return (float) $product->get_regular_price();
    }

    /**
     * Retrieve sale price for simple and variable products.
     *
     * @param WC_Product $product
     *
     * @return float
     */
    protected function getSalePrice(WC_Product $product): float
    {
        if ($product->is_type('variable')) {
            return (float) $product->get_variation_sale_price('min', true);
        }

        return (float) $product->get_sale_price();
    }

    /**
     * Render the badge markup.
     *
     * @param int $value
     * @param array<string, mixed> $attributes
     * @param array<int, string> $extraClasses
     *
     * @return string
     */
    protected function renderBadge(int $value, array $attributes, array $extraClasses = []): string
    {
        $prefix = $this->sanitizeAffix($attributes['prefix'] ?? '-');
        $suffix = $this->sanitizeAffix($attributes['suffix'] ?? '%');

        $classes = array_merge(['jankx-discount-percents'], $extraClasses);
        $classes = array_map('sanitize_html_class', array_filter($classes));

        $wrapperAttributes = get_block_wrapper_attributes([
            'class' => implode(' ', $classes),
            'data-discount' => $value,
        ]);

        $parts = [];

        if ($prefix !== '') {
            $parts[] = sprintf('<span class="discount-prefix">%s</span>', esc_html($prefix));
        }

        $parts[] = sprintf('<span class="discount-value">%s</span>', esc_html(number_format_i18n($value)));

        if ($suffix !== '') {
            $parts[] = sprintf('<span class="discount-suffix">%s</span>', esc_html($suffix));
        }

        return sprintf('<div %1$s>%2$s</div>', $wrapperAttributes, implode('', $parts));
    }

    /**
     * Render a placeholder badge for editor contexts without product data.
     *
     * @param array<string, mixed> $attributes
     *
     * @return string
     */
    protected function renderPlaceholder(array $attributes): string
    {
        if (!is_admin() && !wp_doing_ajax() && !(defined('REST_REQUEST') && REST_REQUEST)) {
            return '';
        }

        // Provide a random preview percentage to illustrate formatting.
        $previewValue = function_exists('wp_rand') ? wp_rand(5, 70) : rand(5, 70);

        return $this->renderBadge($previewValue, $attributes, ['is-preview']);
    }

    /**
     * Sanitize prefix/suffix values.
     *
     * @param string $value
     *
     * @return string
     */
    protected function sanitizeAffix($value): string
    {
        $value = is_string($value) ? $value : '';

        return trim(wp_strip_all_tags($value));
    }
}

