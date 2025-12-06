<?php

namespace Jankx\WooCommerce\Layouts\Gallery;

use Jankx\WooCommerce\Abstracts\Layouts\AbstractProductGalleryLayout;
use WC_Product;

/**
 * Class ModernGalleryLayout
 * 
 * Modern product gallery layout with premium features
 * Features:
 * - Large main image with zoom on hover
 * - Horizontal thumbnail navigation below
 * - Lightbox on click
 * - Smooth transitions
 * - Responsive design
 */
class ModernGalleryLayout extends AbstractProductGalleryLayout
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(
            'modern-gallery',
            __('Modern Gallery', 'jankx-woocommerce'),
            'product-gallery'
        );

        $this->galleryType = 'modern';
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
        
        \Jankx\WooCommerce\Helpers\Logger::debug('ModernGalleryLayout: Rendering main image', [
            'product_id' => $product->get_id(),
            'image_id' => $image_id,
        ]);
        
        if (!$image_id) {
            $placeholder = wc_placeholder_img_src('woocommerce_single');
            \Jankx\WooCommerce\Helpers\Logger::warning('ModernGalleryLayout: No main image, using placeholder', [
                'placeholder_url' => $placeholder,
            ]);
            
            return sprintf(
                '<div class="product-gallery-main modern-gallery-main">
                    <div class="product-image-wrapper" data-zoom="false">
                        <img src="%s" alt="%s" class="main-product-image" />
                        <button class="gallery-nav gallery-prev" aria-label="%s" type="button" style="display: none;">
                            <span class="nav-icon">&#8249;</span>
                        </button>
                        <button class="gallery-nav gallery-next" aria-label="%s" type="button" style="display: none;">
                            <span class="nav-icon">&#8250;</span>
                        </button>
                        <button class="zoom-icon" aria-label="%s" type="button" data-zoom-toggle style="display: none;">
                            <span class="zoom-icon-inner">+</span>
                        </button>
                    </div>
                </div>',
                $placeholder,
                esc_attr($product->get_name()),
                esc_attr__('Previous image', 'jankx-woocommerce'),
                esc_attr__('Next image', 'jankx-woocommerce'),
                esc_attr__('Zoom', 'jankx-woocommerce')
            );
        }

        $full_image_url = wp_get_attachment_image_url($image_id, 'full');
        $single_image_url = wp_get_attachment_image_url($image_id, 'woocommerce_single');
        
        // Get image dimensions
        $image_meta = wp_get_attachment_metadata($image_id);
        $image_width = $image_meta['width'] ?? 800;
        $image_height = $image_meta['height'] ?? 800;
        
        \Jankx\WooCommerce\Helpers\Logger::debug('ModernGalleryLayout: Image URLs', [
            'full_url' => $full_image_url,
            'single_url' => $single_image_url,
            'width' => $image_width,
            'height' => $image_height,
        ]);
        
        // Use large size instead of woocommerce_single to avoid 1x1 issue
        $large_image_url = wp_get_attachment_image_url($image_id, 'large');
        $image_size = $large_image_url ? 'large' : 'woocommerce_single';
        
        $image_html = wp_get_attachment_image($image_id, $image_size, false, [
            'class' => 'main-product-image',
            'alt' => $product->get_name(),
            'data-full-image' => esc_url($full_image_url),
        ]);

        if (empty($image_html) || strpos($image_html, 'width="1"') !== false || strpos($image_html, 'height="1"') !== false) {
            \Jankx\WooCommerce\Helpers\Logger::warning('ModernGalleryLayout: wp_get_attachment_image returned invalid size, using direct img tag');
            $image_html = sprintf(
                '<img src="%s" alt="%s" class="main-product-image" data-full-image="%s" width="%d" height="%d" />',
                esc_url($large_image_url ?: $single_image_url ?: $full_image_url),
                esc_attr($product->get_name()),
                esc_url($full_image_url),
                $image_width,
                $image_height
            );
        }

        return sprintf(
            '<div class="product-gallery-main modern-gallery-main">
                <div class="product-image-wrapper" data-zoom="%s">
                    <a href="%s" class="product-image-link" data-lightbox="product-gallery">
                        %s
                    </a>
                    <button class="gallery-nav gallery-prev" aria-label="%s" type="button">
                        <span class="nav-icon">&#8249;</span>
                    </button>
                    <button class="gallery-nav gallery-next" aria-label="%s" type="button">
                        <span class="nav-icon">&#8250;</span>
                    </button>
                    <button class="zoom-icon" aria-label="%s" type="button" data-zoom-toggle>
                        <span class="zoom-icon-inner">+</span>
                    </button>
                </div>
            </div>',
            $this->supportsZoom ? 'true' : 'false',
            esc_url($full_image_url),
            $image_html,
            esc_attr__('Previous image', 'jankx-woocommerce'),
            esc_attr__('Next image', 'jankx-woocommerce'),
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
        echo '<div class="product-thumbnails modern-thumbnails">';
        echo '<div class="thumbnails-wrapper">';
        
        // Add main image as first thumbnail
        if ($main_image_id) {
            $full_image_url = wp_get_attachment_image_url($main_image_id, 'full');
            $thumbnail_url = wp_get_attachment_image_url($main_image_id, 'woocommerce_gallery_thumbnail');
            
            // Fallback to medium if thumbnail doesn't exist
            if (!$thumbnail_url) {
                $thumbnail_url = wp_get_attachment_image_url($main_image_id, 'medium');
            }
            
            $thumbnail_html = wp_get_attachment_image($main_image_id, 'woocommerce_gallery_thumbnail', false, [
                'class' => 'thumbnail-image',
                'alt' => $product->get_name(),
            ]);
            
            // Check if thumbnail has valid size
            if (empty($thumbnail_html) || strpos($thumbnail_html, 'width="1"') !== false) {
                $image_meta = wp_get_attachment_metadata($main_image_id);
                $thumb_width = 80;
                $thumb_height = 80;
                if ($image_meta && isset($image_meta['width']) && isset($image_meta['height'])) {
                    $ratio = $image_meta['width'] / $image_meta['height'];
                    if ($ratio > 1) {
                        $thumb_height = intval($thumb_width / $ratio);
                    } else {
                        $thumb_width = intval($thumb_height * $ratio);
                    }
                }
                $thumbnail_html = sprintf(
                    '<img src="%s" alt="%s" class="thumbnail-image" width="%d" height="%d" />',
                    esc_url($thumbnail_url ?: $full_image_url),
                    esc_attr($product->get_name()),
                    $thumb_width,
                    $thumb_height
                );
            }
            
            printf(
                '<div class="thumbnail-item active" data-image-id="%d" data-full-image="%s">
                    %s
                </div>',
                $main_image_id,
                esc_url($full_image_url),
                $thumbnail_html
            );
        }

        foreach ($attachment_ids as $attachment_id) {
            $full_image_url = wp_get_attachment_image_url($attachment_id, 'full');
            $thumbnail_url = wp_get_attachment_image_url($attachment_id, 'woocommerce_gallery_thumbnail');
            
            // Fallback to medium if thumbnail doesn't exist
            if (!$thumbnail_url) {
                $thumbnail_url = wp_get_attachment_image_url($attachment_id, 'medium');
            }
            
            $alt_text = get_post_meta($attachment_id, '_wp_attachment_image_alt', true) ?: $product->get_name();
            
            $thumbnail_html = wp_get_attachment_image($attachment_id, 'woocommerce_gallery_thumbnail', false, [
                'class' => 'thumbnail-image',
                'alt' => $alt_text,
            ]);
            
            // Check if thumbnail has valid size
            if (empty($thumbnail_html) || strpos($thumbnail_html, 'width="1"') !== false) {
                $image_meta = wp_get_attachment_metadata($attachment_id);
                $thumb_width = 80;
                $thumb_height = 80;
                if ($image_meta && isset($image_meta['width']) && isset($image_meta['height'])) {
                    $ratio = $image_meta['width'] / $image_meta['height'];
                    if ($ratio > 1) {
                        $thumb_height = intval($thumb_width / $ratio);
                    } else {
                        $thumb_width = intval($thumb_height * $ratio);
                    }
                }
                $thumbnail_html = sprintf(
                    '<img src="%s" alt="%s" class="thumbnail-image" width="%d" height="%d" />',
                    esc_url($thumbnail_url ?: $full_image_url),
                    esc_attr($alt_text),
                    $thumb_width,
                    $thumb_height
                );
            }
            
            printf(
                '<div class="thumbnail-item" data-image-id="%d" data-full-image="%s">
                    %s
                </div>',
                $attachment_id,
                esc_url($full_image_url),
                $thumbnail_html
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
            '.product-gallery-modern-gallery .thumbnail-item {
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
            '.product-gallery-modern-gallery .thumbnails-wrapper {
                grid-template-columns: repeat(%d, 1fr);
            }',
            intval($thumbnail_columns)
        );

        // Zoom level
        $zoom_level = $settings['zoom_level'] ?? 2;
        $css .= sprintf(
            '.product-gallery-modern-gallery .product-image-wrapper[data-zoom="true"]:hover .main-product-image {
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
        const galleries = document.querySelectorAll('.product-gallery-modern-gallery');
        
        galleries.forEach(function(gallery) {
            const mainImage = gallery.querySelector('.main-product-image');
            const thumbnails = gallery.querySelectorAll('.thumbnail-item');
            const imageWrapper = gallery.querySelector('.product-image-wrapper');
            const imageLink = gallery.querySelector('.product-image-link');
            
            if (!mainImage || !imageWrapper) return;
            
            // Get all images (main + thumbnails)
            const allImages = [];
            const mainImageId = mainImage.dataset.fullImage ? mainImage.dataset.fullImage : (imageLink ? imageLink.href : '');
            if (mainImageId) {
                allImages.push({
                    id: mainImage.dataset.imageId || '',
                    fullImage: mainImageId,
                    element: null
                });
            }
            
            thumbnails.forEach(function(thumb) {
                allImages.push({
                    id: thumb.dataset.imageId || '',
                    fullImage: thumb.dataset.fullImage || '',
                    element: thumb
                });
            });
            
            let currentIndex = 0;
            
            // Function to update main image
            const updateMainImage = function(index) {
                if (index < 0) index = allImages.length - 1;
                if (index >= allImages.length) index = 0;
                currentIndex = index;
                
                const imageData = allImages[index];
                if (!imageData || !imageData.fullImage) return;
                
                // Update main image
                mainImage.src = imageData.fullImage;
                mainImage.dataset.fullImage = imageData.fullImage;
                if (mainImage.dataset.imageId !== undefined) {
                    mainImage.dataset.imageId = imageData.id;
                }
                
                if (imageLink) {
                    imageLink.href = imageData.fullImage;
                }
                
                // Update active thumbnail
                thumbnails.forEach(function(t) { t.classList.remove('active'); });
                if (imageData.element) {
                    imageData.element.classList.add('active');
                } else if (index === 0 && thumbnails.length > 0) {
                    // First image is main image, activate first thumbnail
                    thumbnails[0].classList.add('active');
                }
            };
            
            // Prev/Next navigation buttons
            const prevBtn = gallery.querySelector('.gallery-prev');
            const nextBtn = gallery.querySelector('.gallery-next');
            
            console.log('[Modern Gallery] Navigation buttons found:', {
                prevBtn: !!prevBtn,
                nextBtn: !!nextBtn,
                allImagesCount: allImages.length
            });
            
            // Only show navigation if there are multiple images
            if (allImages.length > 1) {
                if (prevBtn) {
                    prevBtn.style.display = 'flex';
                    prevBtn.style.opacity = '0';
                    prevBtn.style.pointerEvents = 'auto'; // Force enable clicks
                    prevBtn.setAttribute('tabindex', '0'); // Make keyboard accessible
                    prevBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        updateMainImage(currentIndex - 1);
                    });
                    // Also handle touch events for mobile
                    prevBtn.addEventListener('touchend', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        updateMainImage(currentIndex - 1);
                    });
                }
                
                if (nextBtn) {
                    nextBtn.style.display = 'flex';
                    nextBtn.style.opacity = '0';
                    nextBtn.style.pointerEvents = 'auto'; // Force enable clicks
                    nextBtn.setAttribute('tabindex', '0'); // Make keyboard accessible
                    nextBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        updateMainImage(currentIndex + 1);
                    });
                    // Also handle touch events for mobile
                    nextBtn.addEventListener('touchend', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        updateMainImage(currentIndex + 1);
                    });
                }
            } else {
                // Hide navigation if only one image
                if (prevBtn) {
                    prevBtn.style.display = 'none';
                }
                if (nextBtn) {
                    nextBtn.style.display = 'none';
                }
            }
            
            // Thumbnail click handler
            thumbnails.forEach(function(thumb, index) {
                thumb.addEventListener('click', function() {
                    const imageId = this.dataset.imageId;
                    const fullImage = this.dataset.fullImage;
                    
                    if (!fullImage) return;
                    
                    // Find index in allImages
                    const thumbIndex = allImages.findIndex(function(img) {
                        return img.element === thumb;
                    });
                    
                    if (thumbIndex >= 0) {
                        updateMainImage(thumbIndex);
                    } else {
                        // Fallback: update directly
                        thumbnails.forEach(function(t) { t.classList.remove('active'); });
                        this.classList.add('active');
                        
                        mainImage.src = fullImage;
                        mainImage.dataset.fullImage = fullImage;
                        
                        if (imageLink) {
                            imageLink.href = fullImage;
                        }
                    }
                });
            });
            
            // Zoom button handler
            const zoomBtn = gallery.querySelector('.zoom-icon[data-zoom-toggle]');
            let isZoomed = false;
            
            if (zoomBtn && imageWrapper.dataset.zoom === 'true') {
                zoomBtn.style.pointerEvents = 'auto'; // Force enable clicks
                zoomBtn.setAttribute('tabindex', '0'); // Make keyboard accessible
                zoomBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    if (imageLink) {
                        // Open lightbox on zoom click
                        imageLink.click();
                    }
                });
                // Also handle touch events for mobile
                zoomBtn.addEventListener('touchend', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    if (imageLink) {
                        imageLink.click();
                    }
                });
            }
            
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
                    lightbox.className = 'modern-lightbox';
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

