<?php

namespace Jankx\WooCommerce\Layouts\CategoryBlock;

use Jankx\WooCommerce\Abstracts\Layouts\AbstractProductCategoryBlockLayout;

/**
 * Class GridCategoryBlockLayout
 * 
 * Grid layout cho category blocks - Default
 */
class GridCategoryBlockLayout extends AbstractProductCategoryBlockLayout
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(
            'grid-category-block',
            __('Grid Category Block', 'jankx-woocommerce'),
            'product-category-block'
        );

        $this->displayType = 'grid';
        $this->showEmptyCategories = false;
        $this->priority = 10;

        $this->supportedSettings = [
            'columns',
            'image_ratio',
            'show_count',
            'show_description',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function generateDynamicCss(array $settings): string
    {
        $css = parent::generateDynamicCss($settings);

        if (isset($settings['show_count']) && !$settings['show_count']) {
            $css .= '.product-count { display: none; }';
        }

        if (isset($settings['show_description']) && !$settings['show_description']) {
            $css .= '.category-description { display: none; }';
        }

        return $css;
    }
}

