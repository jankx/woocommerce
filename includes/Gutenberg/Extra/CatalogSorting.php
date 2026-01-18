<?php

namespace Jankx\WooCommerce\Gutenberg\Extra;

use Jankx\Gutenberg\Extra\AbstractBlockExtra;

/**
 * Class CatalogSorting
 *
 * Handles WooCommerce Catalog Sorting block enhancements with isolated assets.
 * Flatsome-inspired style.
 *
 * @package Jankx\WooCommerce\Gutenberg\Extra
 */
class CatalogSorting extends AbstractBlockExtra
{
    /**
     * @inheritDoc
     */
    public function getTargetBlockName(): string
    {
        return 'woocommerce/catalog-sorting';
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
        $relativePath = 'vendor/jankx/woocommerce/catalog-sorting.css';
        $styleUrl = $this->getAssetUrl($relativePath);

        if ($styleUrl) {
            wp_enqueue_style(
                'jankx-wc-catalog-sorting',
                $styleUrl,
                [],
                $this->getAssetVersion($relativePath)
            );
        }

        // Define design tokens for Sorting block if needed
        $design_tokens = apply_filters('jankx/block/woocommerce/catalog_sorting/tokens', [
            '--jankx-sorting-border-color' => '#ddd',
            '--jankx-sorting-focus-color' => '#1a2b8f',
        ]);

        $style_attr = '';
        foreach ($design_tokens as $name => $value) {
            $style_attr .= "{$name}: {$value}; ";
        }

        // Apply styles to the block container
        if (preg_match('/<([a-z0-9]+)([^>]*class="[^"]*wp-block-woocommerce-catalog-sorting[^"]*"[^>]*)>/i', $block_content, $matches)) {
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
