<?php

namespace Jankx\WooCommerce\Layouts\Gallery;

use Jankx\WooCommerce\Abstracts\Layouts\AbstractProductGalleryLayout;

/**
 * Class SliderGalleryLayout
 * 
 * Slider gallery layout - Default
 */
class SliderGalleryLayout extends AbstractProductGalleryLayout
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(
            'slider-gallery',
            __('Slider Gallery', 'jankx-woocommerce'),
            'product-gallery'
        );

        $this->galleryType = 'slider';
        $this->thumbnailPosition = 'bottom';
        $this->supportsZoom = true;
        $this->supportsLightbox = true;
        $this->priority = 10;

        $this->supportedSettings = [
            'thumbnail_position',
            'thumbnail_size',
            'enable_zoom',
            'enable_lightbox',
            'autoplay',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function generateDynamicCss(array $settings): string
    {
        $css = parent::generateDynamicCss($settings);

        if (isset($settings['thumbnail_position'])) {
            $position = $settings['thumbnail_position'];
            
            if ($position === 'left' || $position === 'right') {
                $css .= '
                    .product-gallery-slider-gallery {
                        display: flex;
                        flex-direction: ' . ($position === 'left' ? 'row-reverse' : 'row') . ';
                    }
                    .product-gallery-slider-gallery .product-thumbnails {
                        flex-direction: column;
                        max-width: 100px;
                    }
                ';
            }
        }

        if (isset($settings['enable_zoom']) && !$settings['enable_zoom']) {
            $css .= '.main-product-image { cursor: default; }';
        }

        return $css;
    }
}

