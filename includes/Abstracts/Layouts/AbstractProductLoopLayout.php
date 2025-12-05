<?php

namespace Jankx\WooCommerce\Abstracts\Layouts;

use Jankx\WooCommerce\Abstracts\AbstractLayout;
use Jankx\WooCommerce\Contracts\Layouts\ProductLoopLayoutInterface;
use WC_Product;

/**
 * Abstract Class AbstractProductLoopLayout
 * 
 * Base implementation cho Product Loop/Grid layouts
 */
abstract class AbstractProductLoopLayout extends AbstractLayout implements ProductLoopLayoutInterface
{
    /**
     * Layout type
     *
     * @var string
     */
    protected $type = 'product-loop';

    /**
     * Hover effects support
     *
     * @var bool
     */
    protected $supportsHoverEffects = true;

    /**
     * Default columns
     *
     * @var int
     */
    protected $defaultColumns = 4;

    /**
     * {@inheritDoc}
     */
    public function render(array $data = []): string
    {
        if (!isset($data['products']) || !is_array($data['products'])) {
            return '';
        }

        $products = $data['products'];
        $options = $data['options'] ?? [];
        
        ob_start();
        echo '<div class="jankx-products-loop ' . esc_attr($this->getId()) . '">';
        
        foreach ($products as $product) {
            if ($product instanceof WC_Product) {
                echo $this->renderProduct($product, $options);
            }
        }
        
        echo '</div>';
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function renderProduct(WC_Product $product, array $options = []): string
    {
        $columns = $options['columns'] ?? $this->defaultColumns;
        $template = $this->getTemplatePath();

        return $this->loadTemplate($template, [
            'product' => $product,
            'layout' => $this,
            'columns' => $columns,
            'columns_class' => $this->getColumnsClass($columns),
            'thumbnail' => $this->renderThumbnail($product),
            'title' => $this->renderTitle($product),
            'price' => $this->renderPrice($product),
            'badges' => $this->renderBadges($product),
            'add_to_cart' => $this->renderAddToCartButton($product),
            'quick_view' => $this->renderQuickView($product),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderThumbnail(WC_Product $product): string
    {
        return $product->get_image('woocommerce_thumbnail');
    }

    /**
     * {@inheritDoc}
     */
    public function renderTitle(WC_Product $product): string
    {
        return sprintf(
            '<h3 class="product-title"><a href="%s">%s</a></h3>',
            esc_url($product->get_permalink()),
            esc_html($product->get_name())
        );
    }

    /**
     * {@inheritDoc}
     */
    public function renderPrice(WC_Product $product): string
    {
        return '<div class="product-price">' . $product->get_price_html() . '</div>';
    }

    /**
     * {@inheritDoc}
     */
    public function renderAddToCartButton(WC_Product $product): string
    {
        ob_start();
        woocommerce_template_loop_add_to_cart(['product' => $product]);
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function renderBadges(WC_Product $product): string
    {
        $badges = [];

        // Sale badge
        if ($product->is_on_sale()) {
            $badges[] = '<span class="badge badge-sale">' . __('Sale', 'jankx-woocommerce') . '</span>';
        }

        // Out of stock badge
        if (!$product->is_in_stock()) {
            $badges[] = '<span class="badge badge-out-of-stock">' . __('Out of Stock', 'jankx-woocommerce') . '</span>';
        }

        // New badge (products created within last 30 days)
        $created = strtotime($product->get_date_created());
        if ($created > strtotime('-30 days')) {
            $badges[] = '<span class="badge badge-new">' . __('New', 'jankx-woocommerce') . '</span>';
        }

        return empty($badges) ? '' : '<div class="product-badges">' . implode('', $badges) . '</div>';
    }

    /**
     * {@inheritDoc}
     */
    public function renderQuickView(WC_Product $product): string
    {
        return sprintf(
            '<button class="quick-view-button" data-product-id="%d">%s</button>',
            $product->get_id(),
            __('Quick View', 'jankx-woocommerce')
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getColumnsClass(int $columns): string
    {
        $classes = [
            'product-item',
            'columns-' . $columns,
            'col-' . (12 / $columns)
        ];

        return implode(' ', $classes);
    }

    /**
     * {@inheritDoc}
     */
    public function supportsHoverEffects(): bool
    {
        return $this->supportsHoverEffects;
    }

    /**
     * {@inheritDoc}
     */
    protected function generateDynamicCss(array $settings): string
    {
        $css = '';

        if (isset($settings['item_spacing'])) {
            $css .= sprintf(
                '.product-loop-%s .product-item { padding: %dpx; }',
                $this->id,
                intval($settings['item_spacing'])
            );
        }

        if (isset($settings['border_radius'])) {
            $css .= sprintf(
                '.product-loop-%s .product-item { border-radius: %dpx; }',
                $this->id,
                intval($settings['border_radius'])
            );
        }

        return $css;
    }
}

