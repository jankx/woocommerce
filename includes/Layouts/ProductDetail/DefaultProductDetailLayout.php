<?php

namespace Jankx\WooCommerce\Layouts\ProductDetail;

use Jankx\WooCommerce\Abstracts\Layouts\AbstractProductDetailLayout;

/**
 * Class DefaultProductDetailLayout
 * 
 * Default product detail layout cho Jankx theme
 */
class DefaultProductDetailLayout extends AbstractProductDetailLayout
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(
            'default-product-detail',
            __('Default Product Detail', 'jankx-woocommerce'),
            'product-detail'
        );

        $this->layoutStructure = 'fullwidth';
        $this->supportsStickyAddToCart = true;
        $this->priority = 10;

        // Supported settings
        $this->supportedSettings = [
            'gallery_width',
            'primary_color',
            'show_related_products',
            'sticky_add_to_cart',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function init(): void
    {
        // Custom hooks for this layout
        add_filter('woocommerce_product_thumbnails_columns', [$this, 'setThumbnailColumns']);
    }

    /**
     * Set thumbnail columns
     *
     * @return int
     */
    public function setThumbnailColumns(): int
    {
        return 4;
    }

    /**
     * {@inheritDoc}
     */
    protected function generateDynamicCss(array $settings): string
    {
        $css = parent::generateDynamicCss($settings);

        // Additional custom CSS for default layout
        if (isset($settings['show_related_products']) && !$settings['show_related_products']) {
            $css .= '.related.products { display: none; }';
        }

        if (isset($settings['sticky_add_to_cart']) && $settings['sticky_add_to_cart']) {
            $css .= '
                .sticky-add-to-cart {
                    position: fixed;
                    bottom: 0;
                    left: 0;
                    right: 0;
                    background: #fff;
                    box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
                    padding: 15px;
                    z-index: 999;
                }
            ';
        }

        return $css;
    }
}

