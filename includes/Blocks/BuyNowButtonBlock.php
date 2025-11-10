<?php

namespace Jankx\WooCommerce\Blocks;

use Jankx\Gutenberg\Block;
use WC_Product;

class BuyNowButtonBlock extends Block
{
    protected $blockId = 'jankx/buynow-button';

    public function __construct()
    {
        parent::__construct(
            realpath(__DIR__ . '/../../blocks/buynow-button')
        );
    }

    /**
     * Render the buy-now button markup.
     *
     * @param array<string,mixed> $attributes
     * @param string $content
     * @param \WP_Block|null $block
     *
     * @return string
     */
    public function render($attributes, $content = '', $block = null)
    {
        $product = $this->resolveProduct($block);

        if (!$product instanceof WC_Product) {
            return $this->renderPlaceholder($attributes);
        }

        if (!$product->is_purchasable()) {
            return $this->renderPlaceholder($attributes, ['requires-configuration']);
        }

        $button_text = $this->sanitizeText($attributes['text'] ?? __('Buy Now', 'jankx'));
        $target_attr = !empty($attributes['openInNewTab']) ? ' target="_blank"' : '';

        $rel_tokens = [];
        if (!empty($attributes['relNoFollow'])) {
            $rel_tokens[] = 'nofollow';
        }
        if (!empty($attributes['relSponsored'])) {
            $rel_tokens[] = 'sponsored';
        }
        if (!empty($rel_tokens)) {
            $rel_attr = sprintf(' rel="%s"', esc_attr(implode(' ', $rel_tokens)));
        } else {
            $rel_attr = '';
        }

        $classes = ['jankx-buynow-button'];
        if ($product->is_type('variable')) {
            $classes[] = 'requires-configuration';
        }

        $wrapper_attributes = get_block_wrapper_attributes([
            'class' => implode(' ', array_map('sanitize_html_class', $classes)),
            'data-product-id' => $product->get_id(),
        ]);

        $url = $this->buildBuyNowUrl($product);

        if (!$url) {
            return $this->renderPlaceholder($attributes, ['requires-configuration']);
        }

        $link = sprintf(
            '<a class="buynow-button__link" href="%1$s"%2$s%3$s>%4$s</a>',
            esc_url($url),
            $target_attr,
            $rel_attr,
            esc_html($button_text)
        );

        return sprintf('<div %1$s>%2$s</div>', $wrapper_attributes, $link);
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

    protected function buildBuyNowUrl(WC_Product $product): ?string
    {
        if ($product->is_type('simple') && $product->is_in_stock()) {
            $url = $product->add_to_cart_url();
            $url = add_query_arg('wc_buy_now', '1', $url);
            return $url;
        }

        if ($product->is_type(['variable', 'grouped', 'external'])) {
            return $product->get_permalink();
        }

        if ($product->is_purchasable() && $product->is_in_stock()) {
            $url = $product->add_to_cart_url();
            $url = add_query_arg('wc_buy_now', '1', $url);
            return $url;
        }

        return null;
    }

    protected function renderPlaceholder(array $attributes, array $extraClasses = []): string
    {
        if (!is_admin() && !wp_doing_ajax() && !(defined('REST_REQUEST') && REST_REQUEST)) {
            return '';
        }

        $classes = array_merge(['jankx-buynow-button', 'is-preview'], $extraClasses);
        $wrapper_attributes = get_block_wrapper_attributes([
            'class' => implode(' ', array_map('sanitize_html_class', $classes)),
        ]);

        $button_text = $this->sanitizeText($attributes['text'] ?? __('Buy Now', 'jankx'));

        $link = sprintf(
            '<button class="buynow-button__link" type="button" disabled>%s</button>',
            esc_html($button_text)
        );

        return sprintf('<div %1$s>%2$s</div>', $wrapper_attributes, $link);
    }

    protected function sanitizeText($value): string
    {
        $value = is_string($value) ? $value : '';

        return trim(wp_strip_all_tags($value));
    }
}

