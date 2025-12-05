<?php

namespace Jankx\WooCommerce\Layouts\ProductLoop;

use Jankx\WooCommerce\Abstracts\Layouts\AbstractProductLoopLayout;
use WC_Product;

/**
 * Class ListProductLoopLayout
 * 
 * List layout cho product loop
 */
class ListProductLoopLayout extends AbstractProductLoopLayout
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(
            'list-product-loop',
            __('List Layout', 'jankx-woocommerce'),
            'product-loop'
        );

        $this->defaultColumns = 1;
        $this->supportsHoverEffects = false;
        $this->priority = 20;

        $this->supportedSettings = [
            'image_position',
            'show_excerpt',
            'item_spacing',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function renderProduct(WC_Product $product, array $options = []): string
    {
        $template = $this->getTemplatePath();

        return $this->loadTemplate($template, [
            'product' => $product,
            'layout' => $this,
            'thumbnail' => $this->renderThumbnail($product),
            'title' => $this->renderTitle($product),
            'price' => $this->renderPrice($product),
            'excerpt' => $this->renderExcerpt($product),
            'badges' => $this->renderBadges($product),
            'add_to_cart' => $this->renderAddToCartButton($product),
        ]);
    }

    /**
     * Render product excerpt
     *
     * @param WC_Product $product
     * @return string
     */
    protected function renderExcerpt(WC_Product $product): string
    {
        $excerpt = $product->get_short_description();
        
        if (empty($excerpt)) {
            return '';
        }

        return '<div class="product-excerpt">' . wp_kses_post($excerpt) . '</div>';
    }

    /**
     * {@inheritDoc}
     */
    protected function generateDynamicCss(array $settings): string
    {
        $css = parent::generateDynamicCss($settings);

        if (isset($settings['image_position']) && $settings['image_position'] === 'right') {
            $css .= '
                .product-loop-list-product-loop .product-item {
                    flex-direction: row-reverse;
                }
            ';
        }

        if (isset($settings['show_excerpt']) && !$settings['show_excerpt']) {
            $css .= '.product-excerpt { display: none; }';
        }

        return $css;
    }
}

