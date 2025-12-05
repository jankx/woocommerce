<?php

namespace Jankx\WooCommerce\Contracts\Layouts;

use Jankx\WooCommerce\Contracts\LayoutInterface;
use WC_Product;

/**
 * Interface ProductLoopLayoutInterface
 * 
 * Interface cho Product Loop/Grid Item layouts
 */
interface ProductLoopLayoutInterface extends LayoutInterface
{
    /**
     * Render single product item trong loop
     *
     * @param WC_Product $product
     * @param array $options Additional options (columns, size, etc.)
     * @return string HTML
     */
    public function renderProduct(WC_Product $product, array $options = []): string;

    /**
     * Render product thumbnail
     *
     * @param WC_Product $product
     * @return string HTML
     */
    public function renderThumbnail(WC_Product $product): string;

    /**
     * Render product title
     *
     * @param WC_Product $product
     * @return string HTML
     */
    public function renderTitle(WC_Product $product): string;

    /**
     * Render product price
     *
     * @param WC_Product $product
     * @return string HTML
     */
    public function renderPrice(WC_Product $product): string;

    /**
     * Render add to cart button
     *
     * @param WC_Product $product
     * @return string HTML
     */
    public function renderAddToCartButton(WC_Product $product): string;

    /**
     * Render product badges (sale, new, out of stock, etc.)
     *
     * @param WC_Product $product
     * @return string HTML
     */
    public function renderBadges(WC_Product $product): string;

    /**
     * Render quick view button
     *
     * @param WC_Product $product
     * @return string HTML
     */
    public function renderQuickView(WC_Product $product): string;

    /**
     * Get columns class for grid
     *
     * @param int $columns
     * @return string CSS class
     */
    public function getColumnsClass(int $columns): string;

    /**
     * Check if layout supports hover effects
     *
     * @return bool
     */
    public function supportsHoverEffects(): bool;
}

