<?php

namespace Jankx\WooCommerce\Layouts\Gallery;

use Jankx\WooCommerce\Abstracts\Layouts\AbstractProductGalleryLayout;
use WC_Product;

/**
 * Class FlatsomeGalleryLayout
 * 
 * Flatsome-inspired product gallery layout
 * Features:
 * - Large main image with zoom on hover
 * - Horizontal thumbnail navigation below
 * - Lightbox on click
 * - Smooth transitions
 * - Responsive design
 */
class FlatsomeGalleryLayout extends AbstractProductGalleryLayout
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(
            'flatsome-gallery',
            __('Flatsome Gallery', 'jankx-woocommerce'),
            'product-gallery'
        );

        $this->galleryType = 'flatsome';
        $this->thumbnailPosition = 'bottom';
        $this->supportsZoom = true;
        $this->supportsLightbox = true;
        $this->priority = 5; // Higher priority than default slider

        $this->supportedSettings = [
            'thumbnail_position',
            'thumbnail_size',
            'enable_zoom',
            'enable_lightbox',
            'zoom_level',
            'thumbnail_columns',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function renderMainImage(WC_Product $product): string
    {
        $image_id = $product->get_image_id();
        
        if (!$image_id) {
            return sprintf(
                '<div class="product-gallery-main flatsome-gallery-main">
                    <img src="%s" alt="%s" class="main-product-image" />
                </div>',
                wc_placeholder_img_src('woocommerce_single'),
                esc_attr($product->get_name())
            );
        }

        $full_image_url = wp_get_attachment_image_url($image_id, 'full');
        $single_image_url = wp_get_attachment_image_url($image_id, 'woocommerce_single');
        
        $image_html = wp_get_attachment_image($image_id, 'woocommerce_single', false, [
            'class' => 'main-product-image',
            'alt' => $product->get_name(),
            'data-full-image' => esc_url($full_image_url),
        ]);

        return sprintf(
            '<div class="product-gallery-main flatsome-gallery-main">
                <div class="product-image-wrapper" data-zoom="%s">
                    <a href="%s" class="product-image-link" data-lightbox="product-gallery">
                        %s
                        <span class="zoom-icon" aria-label="%s"></span>
                    </a>
                </div>
            </div>',
            $this->supportsZoom ? 'true' : 'false',
            esc_url($full_image_url),
            $image_html,
            esc_attr__('Zoom', 'jankx-woocommerce')
        );
    }

    /**
     * {@inheritDoc}
     */
    public function renderThumbnails(WC_Product $product): string
    {
        $attachment_ids = $product->get_gallery_image_ids();
        $main_image_id = $product->get_image_id();
        
        if (empty($attachment_ids) && !$main_image_id) {
            return '';
        }

        ob_start();
        echo '<div class="product-thumbnails flatsome-thumbnails">';
        echo '<div class="thumbnails-wrapper">';
        
        // Add main image as first thumbnail
        if ($main_image_id) {
            $full_image_url = wp_get_attachment_image_url($main_image_id, 'full');
            $single_image_url = wp_get_attachment_image_url($main_image_id, 'woocommerce_single');
            
            printf(
                '<div class="thumbnail-item active" data-image-id="%d" data-full-image="%s">
                    %s
                </div>',
                $main_image_id,
                esc_url($full_image_url),
                wp_get_attachment_image($main_image_id, 'woocommerce_gallery_thumbnail', false, [
                    'class' => 'thumbnail-image',
                    'alt' => $product->get_name(),
                ])
            );
        }

        foreach ($attachment_ids as $attachment_id) {
            $full_image_url = wp_get_attachment_image_url($attachment_id, 'full');
            $alt_text = get_post_meta($attachment_id, '_wp_attachment_image_alt', true) ?: $product->get_name();
            
            printf(
                '<div class="thumbnail-item" data-image-id="%d" data-full-image="%s">
                    %s
                </div>',
                $attachment_id,
                esc_url($full_image_url),
                wp_get_attachment_image($attachment_id, 'woocommerce_gallery_thumbnail', false, [
                    'class' => 'thumbnail-image',
                    'alt' => $alt_text,
                ])
            );
        }
        
        echo '</div>'; // .thumbnails-wrapper
        echo '</div>'; // .product-thumbnails
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    protected function generateDynamicCss(array $settings): string
    {
        $css = parent::generateDynamicCss($settings);

        // Thumbnail size
        $thumbnail_size = $settings['thumbnail_size'] ?? 80;
        $css .= sprintf(
            '.product-gallery-flatsome-gallery .thumbnail-item {
                width: %dpx;
                min-width: %dpx;
                height: %dpx;
            }',
            intval($thumbnail_size),
            intval($thumbnail_size),
            intval($thumbnail_size)
        );

        // Thumbnail columns
        $thumbnail_columns = $settings['thumbnail_columns'] ?? 5;
        $css .= sprintf(
            '.product-gallery-flatsome-gallery .thumbnails-wrapper {
                grid-template-columns: repeat(%d, 1fr);
            }',
            intval($thumbnail_columns)
        );

        // Zoom level
        $zoom_level = $settings['zoom_level'] ?? 2;
        $css .= sprintf(
            '.product-gallery-flatsome-gallery .product-image-wrapper[data-zoom="true"]:hover .main-product-image {
                transform: scale(%s);
            }',
            floatval($zoom_level)
        );

        return $css;
    }

    /**
     * {@inheritDoc}
     */
    public function renderScript(): string
    {
        if (!$this->supportsZoom && !$this->supportsLightbox) {
            return '';
        }

        ob_start();
        ?>
<script>
(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        const galleries = document.querySelectorAll('.product-gallery-flatsome-gallery');
        
        galleries.forEach(function(gallery) {
            const mainImage = gallery.querySelector('.main-product-image');
            const thumbnails = gallery.querySelectorAll('.thumbnail-item');
            const imageWrapper = gallery.querySelector('.product-image-wrapper');
            const imageLink = gallery.querySelector('.product-image-link');
            
            if (!mainImage || !imageWrapper) return;
            
            // Thumbnail click handler
            thumbnails.forEach(function(thumb) {
                thumb.addEventListener('click', function() {
                    const imageId = this.dataset.imageId;
                    const fullImage = this.dataset.fullImage;
                    
                    if (!fullImage) return;
                    
                    // Update active thumbnail
                    thumbnails.forEach(function(t) { t.classList.remove('active'); });
                    this.classList.add('active');
                    
                    // Update main image
                    mainImage.src = fullImage;
                    mainImage.dataset.fullImage = fullImage;
                    
                    if (imageLink) {
                        imageLink.href = fullImage;
                    }
                });
            });
            
            // Zoom on hover
            if (imageWrapper.dataset.zoom === 'true') {
                imageWrapper.addEventListener('mousemove', function(e) {
                    const rect = this.getBoundingClientRect();
                    const x = ((e.clientX - rect.left) / rect.width) * 100;
                    const y = ((e.clientY - rect.top) / rect.height) * 100;
                    
                    mainImage.style.transformOrigin = x + '% ' + y + '%';
                });
            }
            
            // Lightbox (simple implementation)
            if (imageLink && imageLink.dataset.lightbox === 'product-gallery') {
                imageLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const fullImageUrl = this.href;
                    const imageAlt = mainImage.alt || '';
                    
                    // Create lightbox overlay
                    const lightbox = document.createElement('div');
                    lightbox.className = 'flatsome-lightbox';
                    lightbox.innerHTML = '<div class="lightbox-content">' +
                        '<img src="' + fullImageUrl + '" alt="' + imageAlt + '" />' +
                        '<button class="lightbox-close" aria-label="Close">&times;</button>' +
                        '<button class="lightbox-prev" aria-label="Previous">&#8249;</button>' +
                        '<button class="lightbox-next" aria-label="Next">&#8250;</button>' +
                        '</div>';
                    
                    document.body.appendChild(lightbox);
                    document.body.style.overflow = 'hidden';
                    
                    // Close handlers
                    const closeLightbox = function() {
                        document.body.removeChild(lightbox);
                        document.body.style.overflow = '';
                    };
                    
                    lightbox.querySelector('.lightbox-close').addEventListener('click', closeLightbox);
                    lightbox.addEventListener('click', function(e) {
                        if (e.target === lightbox) closeLightbox();
                    });
                    
                    // Navigation
                    const allImages = Array.from(thumbnails).map(function(t) { return t.dataset.fullImage; });
                    let currentIndex = allImages.indexOf(this.href);
                    
                    const showImage = function(index) {
                        if (index < 0) index = allImages.length - 1;
                        if (index >= allImages.length) index = 0;
                        currentIndex = index;
                        lightbox.querySelector('img').src = allImages[index];
                    };
                    
                    lightbox.querySelector('.lightbox-prev').addEventListener('click', function(e) {
                        e.stopPropagation();
                        showImage(currentIndex - 1);
                    });
                    
                    lightbox.querySelector('.lightbox-next').addEventListener('click', function(e) {
                        e.stopPropagation();
                        showImage(currentIndex + 1);
                    });
                    
                    // Keyboard navigation
                    const handleKey = function(e) {
                        if (e.key === 'Escape') closeLightbox();
                        if (e.key === 'ArrowLeft') showImage(currentIndex - 1);
                        if (e.key === 'ArrowRight') showImage(currentIndex + 1);
                    };
                    
                    document.addEventListener('keydown', handleKey);
                    lightbox.addEventListener('remove', function() {
                        document.removeEventListener('keydown', handleKey);
                    });
                });
            }
        });
    });
})();
</script>
        <?php
        return ob_get_clean();
    }
}

