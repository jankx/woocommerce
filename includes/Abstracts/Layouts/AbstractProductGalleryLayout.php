<?php

namespace Jankx\WooCommerce\Abstracts\Layouts;

use Jankx\WooCommerce\Abstracts\AbstractLayout;
use Jankx\WooCommerce\Contracts\Layouts\ProductGalleryLayoutInterface;
use WC_Product;

/**
 * Abstract Class AbstractProductGalleryLayout
 * 
 * Base implementation cho Product Gallery layouts
 */
abstract class AbstractProductGalleryLayout extends AbstractLayout implements ProductGalleryLayoutInterface
{
    /**
     * Layout type
     *
     * @var string
     */
    protected $type = 'product-gallery';

    /**
     * Gallery type
     *
     * @var string
     */
    protected $galleryType = 'slider'; // slider, grid, stacked, horizontal

    /**
     * Thumbnail position
     *
     * @var string
     */
    protected $thumbnailPosition = 'bottom'; // bottom, left, right

    /**
     * Zoom support
     *
     * @var bool
     */
    protected $supportsZoom = true;

    /**
     * Lightbox support
     *
     * @var bool
     */
    protected $supportsLightbox = true;

    /**
     * {@inheritDoc}
     */
    public function render(array $data = []): string
    {
        if (!isset($data['product']) || !$data['product'] instanceof WC_Product) {
            return '';
        }

        return $this->renderGallery($data['product'], $data['options'] ?? []);
    }

    /**
     * {@inheritDoc}
     */
    public function renderGallery(WC_Product $product, array $options = []): string
    {
        $template = $this->getTemplatePath();

        return $this->loadTemplate($template, [
            'product' => $product,
            'layout' => $this,
            'main_image' => $this->renderMainImage($product),
            'thumbnails' => $this->renderThumbnails($product),
            'video' => $this->renderVideo($product),
            'view_360' => $this->render360View($product),
            'gallery_type' => $this->galleryType,
            'thumbnail_position' => $this->thumbnailPosition,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderMainImage(WC_Product $product): string
    {
        $image_id = $product->get_image_id();
        
        if (!$image_id) {
            return sprintf(
                '<img src="%s" alt="%s" class="main-product-image" />',
                wc_placeholder_img_src(),
                esc_attr($product->get_name())
            );
        }

        return wp_get_attachment_image($image_id, 'woocommerce_single', false, [
            'class' => 'main-product-image',
            'alt' => $product->get_name(),
            'data-zoom' => $this->supportsZoom ? 'true' : 'false',
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderThumbnails(WC_Product $product): string
    {
        $attachment_ids = $product->get_gallery_image_ids();
        
        if (empty($attachment_ids)) {
            return '';
        }

        ob_start();
        echo '<div class="product-thumbnails">';
        
        // Add main image as first thumbnail
        $main_image_id = $product->get_image_id();
        if ($main_image_id) {
            echo wp_get_attachment_image($main_image_id, 'woocommerce_gallery_thumbnail', false, [
                'class' => 'thumbnail-item active',
                'data-image-id' => $main_image_id
            ]);
        }

        foreach ($attachment_ids as $attachment_id) {
            echo wp_get_attachment_image($attachment_id, 'woocommerce_gallery_thumbnail', false, [
                'class' => 'thumbnail-item',
                'data-image-id' => $attachment_id
            ]);
        }
        
        echo '</div>';
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function renderVideo(WC_Product $product): string
    {
        $video_url = get_post_meta($product->get_id(), '_product_video_url', true);
        
        if (empty($video_url)) {
            return '';
        }

        return sprintf(
            '<div class="product-video"><video src="%s" controls></video></div>',
            esc_url($video_url)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function render360View(WC_Product $product): string
    {
        // Placeholder for 360 view feature
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function getGalleryType(): string
    {
        return $this->galleryType;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsZoom(): bool
    {
        return $this->supportsZoom;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsLightbox(): bool
    {
        return $this->supportsLightbox;
    }

    /**
     * {@inheritDoc}
     */
    public function getThumbnailPosition(): string
    {
        return $this->thumbnailPosition;
    }

    /**
     * {@inheritDoc}
     */
    protected function generateDynamicCss(array $settings): string
    {
        $css = '';

        if (isset($settings['gallery_width'])) {
            $css .= sprintf(
                '.product-gallery-%s { max-width: %s%%; }',
                $this->id,
                intval($settings['gallery_width'])
            );
        }

        if (isset($settings['thumbnail_size'])) {
            $css .= sprintf(
                '.product-gallery-%s .thumbnail-item { width: %dpx; height: %dpx; }',
                $this->id,
                intval($settings['thumbnail_size']),
                intval($settings['thumbnail_size'])
            );
        }

        return $css;
    }
}

