<?php

namespace Jankx\WooCommerce\Gutenberg\Extra;

use Jankx\Gutenberg\Extra\AbstractBlockExtra;

/**
 * Class ProductPrice
 *
 * Handles WooCommerce Product Price block enhancements with isolated assets.
 * Tiki & Shopee inspired style.
 *
 * @package Jankx\WooCommerce\Gutenberg\Extra
 */
class ProductPrice extends AbstractBlockExtra
{
    /**
     * @inheritDoc
     */
    public function getTargetBlockName(): string
    {
        return 'woocommerce/product-price';
    }

    /**
     * Handle the block rendering.
     *
     * @param string $block_content
     * @param array $block
     * @return string
     */
    public function handle(string $block_content, array $block): string
    {
        // Enqueue the isolated CSS file
        $relativePath = 'vendor/jankx/woocommerce/product-price.css';
        $styleUrl = $this->getAssetUrl($relativePath);

        if ($styleUrl) {
            wp_enqueue_style(
                'jankx-wc-product-price',
                $styleUrl,
                [],
                $this->getAssetVersion($relativePath)
            );
        }

        // Define design tokens for Price
        $design_tokens = apply_filters('jankx/block/woocommerce/product_price/tokens', [
            '--jankx-price-color' => '#ff424e', // Tiki Red
        ]);

        $style_attr = '';
        foreach ($design_tokens as $name => $value) {
            $style_attr .= "{$name}: {$value}; ";
        }

        // Apply styles to the block container (Improved regex: removed ^ and added support for leading whitespace)
        if (preg_match('/<([a-z0-9]+)([^>]*class="[^"]*wp-block-woocommerce-product-price[^"]*"[^>]*)>/i', $block_content, $matches)) {
            $tag_name = $matches[1];
            $attributes = $matches[2];

            if (preg_match('/style="([^"]*)"/i', $attributes, $style_matches)) {
                $existing_style = $style_matches[1];
                $new_style = rtrim($existing_style, '; ') . '; ' . $style_attr;
                $new_attributes = str_replace($style_matches[0], 'style="' . $new_style . '"', $attributes);
            } else {
                $new_attributes = $attributes . ' style="' . trim($style_attr) . '" ';
            }

            $new_opening_tag = "<{$tag_name}{$new_attributes}>";
            $block_content = str_replace($matches[0], $new_opening_tag, $block_content);
        }

        return $block_content;
    }
}
