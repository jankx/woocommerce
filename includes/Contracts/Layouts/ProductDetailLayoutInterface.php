<?php

namespace Jankx\WooCommerce\Contracts\Layouts;

use Jankx\WooCommerce\Contracts\LayoutInterface;
use WC_Product;

/**
 * Interface ProductDetailLayoutInterface
 * 
 * Interface cho Product Detail Page layouts
 */
interface ProductDetailLayoutInterface extends LayoutInterface
{
    /**
     * Render product gallery
     *
     * @param WC_Product $product
     * @return string HTML
     */
    public function renderGallery(WC_Product $product): string;

    /**
     * Render product summary
     *
     * @param WC_Product $product
     * @return string HTML
     */
    public function renderSummary(WC_Product $product): string;

    /**
     * Render product meta
     *
     * @param WC_Product $product
     * @return string HTML
     */
    public function renderMeta(WC_Product $product): string;

    /**
     * Render product tabs
     *
     * @param WC_Product $product
     * @return string HTML
     */
    public function renderTabs(WC_Product $product): string;

    /**
     * Render related products
     *
     * @param WC_Product $product
     * @return string HTML
     */
    public function renderRelatedProducts(WC_Product $product): string;

    /**
     * Get layout structure (sidebar, fullwidth, etc.)
     *
     * @return string
     */
    public function getLayoutStructure(): string;

    /**
     * Check if layout supports sticky add to cart
     *
     * @return bool
     */
    public function supportsStickyAddToCart(): bool;
}

