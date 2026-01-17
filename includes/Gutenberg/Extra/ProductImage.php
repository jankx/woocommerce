<?php

namespace Jankx\WooCommerce\Gutenberg\Extra;

use Jankx\Gutenberg\Extra\AbstractBlockExtra;

/**
 * Class ProductImage
 *
 * Handles WooCommerce Product Image block enhancements with isolated assets.
 * This class is located within the jankx/woocommerce package.
 *
 * @package Jankx\WooCommerce\Gutenberg\Extra
 */
class ProductImage extends AbstractBlockExtra
{
    /**
     * @inheritDoc
     */
    public function getTargetBlockName(): string
    {
        return 'woocommerce/product-image';
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
        /**
         * Enqueue the isolated CSS file.
         * The relative path is within the theme/child theme structure.
         */
        $relativePath = 'vendor/jankx/woocommerce/product-image.css';
        $styleUrl = $this->getAssetUrl($relativePath);

        if ($styleUrl) {
            wp_enqueue_style(
                'jankx-wc-product-image',
                $styleUrl,
                [],
                $this->getAssetVersion($relativePath)
            );
        }

        // Add specific design tokens for images if needed via filter
        $design_tokens = apply_filters('jankx/block/woocommerce/product_image/tokens', [
            '--jankx-image-shadow' => '0 10px 15px -3px rgba(0, 0, 0, 0.1)',
        ]);

        $style_attr = '';
        foreach ($design_tokens as $name => $value) {
            $style_attr .= "{$name}: {$value}; ";
        }

        // Apply styles to the block container
        if (preg_match('/^<([a-z0-9]+)([^>]*class="[^"]*wp-block-woocommerce-product-image[^"]*"[^>]*)>/i', $block_content, $matches)) {
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
