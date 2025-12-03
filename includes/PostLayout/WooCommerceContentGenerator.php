<?php

namespace Jankx\WooCommerce\PostLayout;

use Jankx\Gutenberg\Blocks\PostLayoutTemplateBlock;
use Jankx\Layouts\PostLayout\Generators\AbstractContentGenerator;
use WP_Query;

/**
 * WooCommerce Content Generator
 */
class WooCommerceContentGenerator extends AbstractContentGenerator
{
    protected $name = 'woocommerce';
    protected $title = 'WooCommerce Products';

    protected $supportedOptions = [
        'columns',
        'showFeaturedImage',
        'showTitle',
        'showPrice',
        'showRating',
        'showAddToCart',
        'showSaleBadge',
        'postsPerPage',
        'imageSize',
        'imageRatio',
        'postTemplate',
    ];

    protected function isWooCommerceActive(): bool
    {
        return class_exists('WooCommerce');
    }

    protected function renderContent(WP_Query $query, array $options = []): string
    {
        if (!$this->isWooCommerceActive()) {
            return '<div class="woocommerce-error">' . __('WooCommerce is not active', 'jankx') . '</div>';
        }

        if (!$query->have_posts()) {
            return '<div class="no-products">' . __('No products found.', 'woocommerce') . '</div>';
        }

        $options = $this->normalizeOptions($options);
        $templateBlock = $options['postTemplate'] ?? null;

        if (!is_array($templateBlock) || empty($templateBlock)) {
            $templateBlock = $this->getDefaultTemplateBlock($options);
        }

        // Ensure template block has innerBlocks, if not, build them from attributes
        if (is_array($templateBlock) && empty($templateBlock['innerBlocks'])) {
            $innerBlocks = $this->buildInnerBlocksFromOptions($options);
            if (!empty($innerBlocks)) {
                $templateBlock['innerBlocks'] = $innerBlocks;
            }
        }

        if (!is_array($templateBlock) || empty($templateBlock)) {
            return '';
        }

        $options['postTemplate'] = $templateBlock;

        $html = PostLayoutTemplateBlock::renderTemplateWithQuery(
            $templateBlock,
            $query,
            $options,
            $this->getLayout()
        );

        $layout = $this->getLayout();
        if ($layout) {
            return $layout->wrapTemplateHtml($html, $options);
        }

        return $html;
    }

    protected function renderPreviewContent(array $options = []): array
    {
        return [
            'name' => $this->name,
            'title' => $this->title,
            'type' => 'woocommerce',
            'columns' => $options['columns'] ?? 3,
            'supportedOptions' => $this->supportedOptions,
            'previewItems' => $this->generatePreviewItems($options),
            'woocommerce' => true,
        ];
    }

    public function wrapCarouselHtml(WP_Query $query, array $options, string $carouselHtml): string
    {
        if (!empty($options['postTemplate'])) {
            return $carouselHtml;
        }

        if (!$this->isWooCommerceActive()) {
            return $carouselHtml;
        }

        ob_start();
        ?>
        <div data-wp-interactive="woocommerce/product-collection" data-wp-context='{"notices":[]}' class="wp-block-woocommerce-product-collection is-layout-flow wp-block-product-collection-is-layout-flow">
            <div data-wp-interactive="woocommerce/store-notices" class="wc-block-components-notices alignwide"></div>
            <?php echo $carouselHtml; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
        <?php
        return ob_get_clean();
    }

   public function getName(): string
    {
        return $this->name;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function supportsOptions(array $options): bool
    {
        if (empty($this->supportedOptions)) {
            return true;
        }

        foreach ($options as $key => $value) {
            if ($value !== false && !in_array($key, $this->supportedOptions, true)) {
                return false;
            }
        }

        return true;
    }

    protected function generatePreviewItems(array $options = []): array
    {
        $count = min($options['postsPerPage'] ?? 6, 6);
        $items = [];

        for ($i = 0; $i < $count; $i++) {
            $items[] = [
                'id' => $i + 1,
                'title' => sprintf(__('Product %d', 'jankx'), $i + 1),
                'price' => '$' . (29 + $i * 10) . '.99',
                'rating' => 4.0 + ($i % 2) * 0.5,
                'on_sale' => ($i % 3 === 0),
                'thumbnail' => true,
            ];
        }

        return $items;
    }

    protected function normalizeOptions(array $options): array
    {
        if (empty($options['itemsWrapperClass'])) {
            $options['itemsWrapperClass'] = 'wc-block-product-template';
        }

        if (empty($options['itemClass'])) {
            $options['itemClass'] = 'wc-block-product';
        }

        return $options;
    }

    protected function getDefaultTemplateBlock(array $options): array
    {
        // Try to build from parsed blocks first
        $content = $this->buildDefaultTemplateContent($options);

        if (!empty($content)) {
            $blocks = parse_blocks($content);
            if (!empty($blocks) && !empty($blocks[0])) {
                $templateBlock = $blocks[0];
                // Ensure innerBlocks exist
                if (empty($templateBlock['innerBlocks'])) {
                    $templateBlock['innerBlocks'] = $this->buildInnerBlocksFromOptions($options);
                }
                return $templateBlock;
            }
        }

        // Fallback: build directly from options
        $innerBlocks = $this->buildInnerBlocksFromOptions($options);
        if (empty($innerBlocks)) {
            return [];
        }

        return [
            'blockName' => 'jankx/post-layout-template',
            'attrs' => [
                'className' => 'wc-block-product-template wc-block-product-template--default',
            ],
            'innerBlocks' => $innerBlocks,
            'innerHTML' => '',
            'innerContent' => [],
        ];
    }

    protected function buildDefaultTemplateContent(array $options): string
    {
        $blocks = [];

        $showFeaturedImage = $options['showFeaturedImage'] ?? true;
        $showSaleBadge = $options['showSaleBadge'] ?? true;
        $showTitle = $options['showTitle'] ?? true;
        $showPrice = $options['showPrice'] ?? true;
        $showRating = $options['showRating'] ?? false;
        $showAddToCart = $options['showAddToCart'] ?? true;

        if ($showFeaturedImage) {
            $blocks[] = sprintf(
                '<!-- wp:woocommerce/product-image {"align":"center","showSaleBadge":%s} /-->',
                $showSaleBadge ? 'true' : 'false'
            );
        }

        if ($showTitle) {
            $blocks[] = '<!-- wp:post-title {"textAlign":"center","isLink":true,"style":{"spacing":{"margin":{"bottom":"0.75rem","top":"0"}},"typography":{"lineHeight":"1.4"}},"fontSize":"medium","__woocommerceNamespace":"woocommerce/product-collection/product-title"} /-->';
        }

        if ($showRating) {
            $blocks[] = '<!-- wp:woocommerce/product-rating {"textAlign":"center"} /-->';
        }

        if ($showPrice) {
            $blocks[] = '<!-- wp:woocommerce/product-price {"textAlign":"center","fontSize":"small"} /-->';
        }

        if ($showAddToCart) {
            $blocks[] = '<!-- wp:woocommerce/product-button {"textAlign":"center","fontSize":"small"} /-->';
        }

        if (empty($blocks)) {
            $blocks[] = '<!-- wp:woocommerce/product-title {"textAlign":"center"} /-->';
        }

        $wrapperAttrs = [
            'className' => 'wc-block-product-template wc-block-product-template--default',
        ];

        return sprintf(
            '<!-- wp:jankx/post-layout-template %s -->%s<!-- /wp:jankx/post-layout-template -->',
            wp_json_encode($wrapperAttrs),
            implode('', $blocks)
        );
    }


    /**
     * Append classes to the wrapper
     *
     * @param array<string> $classes
     * @param array $options
     * @return array<string> Updated classes list
     */
    public function appendClassesToWrapper(array $classes, array $options = []): array
    {
        // Add this class for compatibility with WooCommerce CSS for the "woocommerce/product-collection" block
        if (empty($options['postTemplate'])) {
            $classes[] = 'wp-block-woocommerce-product-collection';
        }

        return $classes;
    }

    /**
     * Build inner blocks from options as fallback
     *
     * @param array $options
     * @return array
     */
    protected function buildInnerBlocksFromOptions(array $options): array
    {
        $innerBlocks = [];

        $showFeaturedImage = $options['showFeaturedImage'] ?? true;
        $showSaleBadge = $options['showSaleBadge'] ?? true;
        $showTitle = $options['showTitle'] ?? true;
        $showPrice = $options['showPrice'] ?? true;
        $showRating = $options['showRating'] ?? false;
        $showAddToCart = $options['showAddToCart'] ?? true;

        if ($showFeaturedImage) {
            $innerBlocks[] = [
                'blockName' => 'woocommerce/product-image',
                'attrs' => [
                    'align' => 'center',
                    'showSaleBadge' => $showSaleBadge,
                ],
            ];
        }

        if ($showTitle) {
            $innerBlocks[] = [
                'blockName' => 'woocommerce/product-title',
                'attrs' => [
                    'textAlign' => 'center',
                    'level' => 3,
                ],
            ];
        }

        if ($showRating) {
            $innerBlocks[] = [
                'blockName' => 'woocommerce/product-rating',
                'attrs' => [
                    'textAlign' => 'center',
                ],
            ];
        }

        if ($showPrice) {
            $innerBlocks[] = [
                'blockName' => 'woocommerce/product-price',
                'attrs' => [
                    'textAlign' => 'center',
                    'fontSize' => 'small',
                ],
            ];
        }

        if ($showAddToCart) {
            $innerBlocks[] = [
                'blockName' => 'woocommerce/product-button',
                'attrs' => [
                    'textAlign' => 'center',
                    'fontSize' => 'small',
                ],
            ];
        }

        // Ensure at least title is shown
        if (empty($innerBlocks)) {
            $innerBlocks[] = [
                'blockName' => 'woocommerce/product-title',
                'attrs' => [
                    'textAlign' => 'center',
                ],
            ];
        }

        return $innerBlocks;
    }
}

