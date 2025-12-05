<?php

namespace Jankx\WooCommerce\Abstracts\Layouts;

use Jankx\WooCommerce\Abstracts\AbstractLayout;
use Jankx\WooCommerce\Contracts\Layouts\ProductDetailLayoutInterface;
use WC_Product;

/**
 * Abstract Class AbstractProductDetailLayout
 * 
 * Base implementation cho Product Detail layouts
 */
abstract class AbstractProductDetailLayout extends AbstractLayout implements ProductDetailLayoutInterface
{
    /**
     * Layout type
     *
     * @var string
     */
    protected $type = 'product-detail';

    /**
     * Layout structure
     *
     * @var string
     */
    protected $layoutStructure = 'fullwidth'; // fullwidth, sidebar-left, sidebar-right

    /**
     * Sticky add to cart support
     *
     * @var bool
     */
    protected $supportsStickyAddToCart = false;

    /**
     * {@inheritDoc}
     */
    public function render(array $data = []): string
    {
        if (!isset($data['product']) || !$data['product'] instanceof WC_Product) {
            return '';
        }

        $product = $data['product'];
        $template = $this->getTemplatePath();

        return $this->loadTemplate($template, [
            'product' => $product,
            'layout' => $this,
            'gallery' => $this->renderGallery($product),
            'summary' => $this->renderSummary($product),
            'meta' => $this->renderMeta($product),
            'tabs' => $this->renderTabs($product),
            'related' => $this->renderRelatedProducts($product),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderGallery(WC_Product $product): string
    {
        // Default implementation, có thể override
        ob_start();
        woocommerce_show_product_images();
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function renderSummary(WC_Product $product): string
    {
        // Default implementation, có thể override
        ob_start();
        woocommerce_template_single_title();
        woocommerce_template_single_rating();
        woocommerce_template_single_price();
        woocommerce_template_single_excerpt();
        woocommerce_template_single_add_to_cart();
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function renderMeta(WC_Product $product): string
    {
        // Default implementation
        ob_start();
        woocommerce_template_single_meta();
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function renderTabs(WC_Product $product): string
    {
        // Default implementation
        ob_start();
        woocommerce_output_product_data_tabs();
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function renderRelatedProducts(WC_Product $product): string
    {
        // Default implementation
        ob_start();
        woocommerce_output_related_products();
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function getLayoutStructure(): string
    {
        return $this->layoutStructure;
    }

    /**
     * Set layout structure
     *
     * @param string $structure
     * @return self
     */
    public function setLayoutStructure(string $structure): self
    {
        $this->layoutStructure = $structure;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsStickyAddToCart(): bool
    {
        return $this->supportsStickyAddToCart;
    }

    /**
     * Set sticky add to cart support
     *
     * @param bool $supports
     * @return self
     */
    public function setStickyAddToCartSupport(bool $supports): self
    {
        $this->supportsStickyAddToCart = $supports;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function generateDynamicCss(array $settings): string
    {
        $css = '';

        // Generate CSS based on settings
        if (isset($settings['gallery_width'])) {
            $css .= sprintf(
                '.product-detail-%s .product-gallery { width: %s%%; }',
                $this->id,
                intval($settings['gallery_width'])
            );
        }

        if (isset($settings['primary_color'])) {
            $css .= sprintf(
                '.product-detail-%s .button { background-color: %s; }',
                $this->id,
                esc_attr($settings['primary_color'])
            );
        }

        return $css;
    }
}

