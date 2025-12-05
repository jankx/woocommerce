<?php

namespace Jankx\WooCommerce\Contracts\Layouts;

use Jankx\WooCommerce\Contracts\LayoutInterface;
use WC_Product;

/**
 * Interface ProductGalleryLayoutInterface
 * 
 * Interface cho Product Gallery Detail layouts
 */
interface ProductGalleryLayoutInterface extends LayoutInterface
{
    /**
     * Render product gallery
     *
     * @param WC_Product $product
     * @param array $options Gallery options
     * @return string HTML
     */
    public function renderGallery(WC_Product $product, array $options = []): string;

    /**
     * Render main image
     *
     * @param WC_Product $product
     * @return string HTML
     */
    public function renderMainImage(WC_Product $product): string;

    /**
     * Render thumbnail navigation
     *
     * @param WC_Product $product
     * @return string HTML
     */
    public function renderThumbnails(WC_Product $product): string;

    /**
     * Render video trong gallery (nếu có)
     *
     * @param WC_Product $product
     * @return string HTML
     */
    public function renderVideo(WC_Product $product): string;

    /**
     * Render 360 view (nếu support)
     *
     * @param WC_Product $product
     * @return string HTML
     */
    public function render360View(WC_Product $product): string;

    /**
     * Get gallery type (slider, grid, stacked, etc.)
     *
     * @return string
     */
    public function getGalleryType(): string;

    /**
     * Check if supports zoom
     *
     * @return bool
     */
    public function supportsZoom(): bool;

    /**
     * Check if supports lightbox
     *
     * @return bool
     */
    public function supportsLightbox(): bool;

    /**
     * Get thumbnail position (bottom, left, right)
     *
     * @return string
     */
    public function getThumbnailPosition(): string;
}

