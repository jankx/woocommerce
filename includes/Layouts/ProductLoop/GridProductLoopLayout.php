<?php

namespace Jankx\WooCommerce\Layouts\ProductLoop;

use Jankx\WooCommerce\Abstracts\Layouts\AbstractProductLoopLayout;

/**
 * Class GridProductLoopLayout
 * 
 * Grid layout cho product loop - Default layout
 */
class GridProductLoopLayout extends AbstractProductLoopLayout
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(
            'grid-product-loop',
            __('Grid Layout', 'jankx-woocommerce'),
            'product-loop'
        );

        $this->defaultColumns = 4;
        $this->supportsHoverEffects = true;
        $this->priority = 10;

        $this->supportedSettings = [
            'columns',
            'item_spacing',
            'border_radius',
            'show_quick_view',
            'hover_effect',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function generateDynamicCss(array $settings): string
    {
        $css = parent::generateDynamicCss($settings);

        if (isset($settings['hover_effect']) && $settings['hover_effect'] === 'zoom') {
            $css .= '
                .product-loop-grid-product-loop .product-item:hover img {
                    transform: scale(1.05);
                    transition: transform 0.3s ease;
                }
            ';
        }

        if (isset($settings['show_quick_view']) && !$settings['show_quick_view']) {
            $css .= '.quick-view-button { display: none; }';
        }

        return $css;
    }
}

