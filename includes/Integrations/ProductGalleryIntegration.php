<?php

namespace Jankx\WooCommerce\Integrations;

use Jankx\WooCommerce\Helpers\Logger;
use Jankx\WooCommerce\LayoutSystem\LayoutManager;

/**
 * Class ProductGalleryIntegration
 * 
 * Integration để apply custom layout cho WooCommerce Product Gallery
 * 
 * Hooks vào:
 * - woocommerce_single_product_image_html
 * - woocommerce_product_thumbnails
 */
class ProductGalleryIntegration
{
    /**
     * @var bool Enabled status
     */
    private static $enabled = false;

    /**
     * @var string Layout ID to use
     */
    private static $layoutId = 'modern-gallery';

    /**
     * @var LayoutManager
     */
    private static $layoutManager;

    /**
     * Enable integration
     *
     * @param string $layoutId Layout ID to use (default: modern-gallery)
     * @return void
     */
    public static function enable(string $layoutId = 'modern-gallery'): void
    {
        self::$enabled = true;
        self::$layoutId = $layoutId;
        self::$layoutManager = LayoutManager::getInstance();

        Logger::info('ProductGalleryIntegration: Enabled', [
            'layout_id' => $layoutId,
        ]);

        self::registerHooks();
    }

    /**
     * Disable integration
     *
     * @return void
     */
    public static function disable(): void
    {
        self::$enabled = false;
        Logger::info('ProductGalleryIntegration: Disabled');
    }

    /**
     * Set layout to use
     *
     * @param string $layoutId
     * @return void
     */
    public static function useLayout(string $layoutId): void
    {
        self::$layoutId = $layoutId;
        
        Logger::debug('ProductGalleryIntegration: Layout changed', [
            'layout_id' => $layoutId,
        ]);
    }

    /**
     * Check if enabled
     *
     * @return bool
     */
    public static function isEnabled(): bool
    {
        return self::$enabled;
    }

    /**
     * Register hooks
     *
     * @return void
     */
    private static function registerHooks(): void
    {
        // Remove default WooCommerce gallery output
        remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20);
        
        // Add custom gallery output
        add_action('woocommerce_before_single_product_summary', [__CLASS__, 'renderProductGallery'], 20);
        
        // Filter block content for woocommerce/product-image-gallery block
        // Priority 20 to run after block is fully rendered
        add_filter('render_block', [__CLASS__, 'filterBlockOutput'], 20, 2);
        
        // Filter để set layout
        add_filter('jankx_woocommerce_product_gallery_layout', function() {
            return self::$layoutId;
        });
    }

    /**
     * Render product gallery with custom layout
     *
     * @return void
     */
    public static function renderProductGallery(): void
    {
        if (!self::$enabled) {
            // Fallback to default WooCommerce gallery
            woocommerce_show_product_images();
            return;
        }

        global $product;

        if (!$product || !is_a($product, 'WC_Product')) {
            Logger::warning('ProductGalleryIntegration: No product found');
            woocommerce_show_product_images();
            return;
        }

        Logger::debug('ProductGalleryIntegration: Rendering gallery', [
            'product_id' => $product->get_id(),
            'layout_id' => self::$layoutId,
        ]);

        // Get layout
        $layout = self::$layoutManager->get(self::$layoutId);
        
        if (!$layout) {
            Logger::warning('ProductGalleryIntegration: Layout not found', [
                'layout_id' => self::$layoutId,
            ]);
            woocommerce_show_product_images();
            return;
        }

        // Get settings from config
        $settings = self::getSettings();

        // Inject CSS for this layout
        $cssManager = \Jankx\WooCommerce\LayoutSystem\CssManager::getInstance();
        $cssManager->injectLayoutCss($layout, $settings);

        // Render layout
        try {
            $output = $layout->render([
                'product' => $product,
                'settings' => $settings,
            ]);

            if (!empty($output)) {
                echo $output;
                Logger::info('ProductGalleryIntegration: Gallery rendered', [
                    'layout_id' => self::$layoutId,
                    'output_length' => strlen($output),
                ]);
            } else {
                Logger::warning('ProductGalleryIntegration: Empty output from layout');
                woocommerce_show_product_images();
            }
        } catch (\Exception $e) {
            Logger::error('ProductGalleryIntegration: Error rendering gallery', [
                'error' => $e->getMessage(),
                'layout_id' => self::$layoutId,
            ]);
            woocommerce_show_product_images();
        }
    }

    /**
     * Get settings from config
     *
     * @return array
     */
    private static function getSettings(): array
    {
        $hasApp = function_exists('app');
        $configBound = $hasApp && app()->bound('woocommerce.layout.config');

        if ($configBound) {
            $config = app('woocommerce.layout.config');
            return $config->get('product_gallery.settings', []);
        }

        // Fallback: Load config directly from file
        $configPath = get_template_directory() . '/config/woocomerce.php';
        if (file_exists($configPath)) {
            $config = include $configPath;
            return $config['product_gallery']['settings'] ?? [];
        }

        return [];
    }

    /**
     * Filter block output for product gallery block
     *
     * @param string $block_content
     * @param array $block
     * @return string
     */
    public static function filterBlockOutput(string $block_content, array $block): string
    {
        // Check for WooCommerce product image gallery block
        $blockName = $block['blockName'] ?? '';
        $isGalleryBlock = ($blockName === 'woocommerce/product-image-gallery');
        
        // Also check for block content with data-block-name attribute
        $hasGalleryBlockContent = (strpos($block_content, 'data-block-name="woocommerce/product-image-gallery"') !== false);
        
        if (!$isGalleryBlock && !$hasGalleryBlockContent) {
            return $block_content;
        }

        Logger::info('ProductGalleryIntegration: Block detected', [
            'block_name' => $blockName,
            'is_gallery_block' => $isGalleryBlock,
            'has_gallery_content' => $hasGalleryBlockContent,
            'enabled' => self::$enabled,
            'content_length' => strlen($block_content),
        ]);

        if (!self::$enabled) {
            Logger::warning('ProductGalleryIntegration: Integration not enabled');
            return $block_content;
        }

        global $product;

        if (!$product || !is_a($product, 'WC_Product')) {
            // Try to get product from post
            $product_id = get_the_ID();
            if ($product_id) {
                $product = wc_get_product($product_id);
            }
        }

        if (!$product || !is_a($product, 'WC_Product')) {
            Logger::warning('ProductGalleryIntegration: No product found in block filter', [
                'post_id' => get_the_ID(),
            ]);
            return $block_content;
        }

        Logger::info('ProductGalleryIntegration: Filtering block output', [
            'block_name' => $block['blockName'],
            'product_id' => $product->get_id(),
            'layout_id' => self::$layoutId,
        ]);

        // Get layout
        $layout = self::$layoutManager->get(self::$layoutId);
        
        if (!$layout) {
            Logger::warning('ProductGalleryIntegration: Layout not found in block filter', [
                'layout_id' => self::$layoutId,
            ]);
            return $block_content;
        }

        // Get settings from config
        $settings = self::getSettings();

        // Inject CSS for this layout
        $cssManager = \Jankx\WooCommerce\LayoutSystem\CssManager::getInstance();
        $cssManager->injectLayoutCss($layout, $settings);

        // Render layout
        try {
            $output = $layout->render([
                'product' => $product,
                'settings' => $settings,
            ]);

            if (!empty($output)) {
                Logger::info('ProductGalleryIntegration: Block replaced with custom layout', [
                    'layout_id' => self::$layoutId,
                    'output_length' => strlen($output),
                ]);
                return $output;
            } else {
                Logger::warning('ProductGalleryIntegration: Empty output from layout in block filter');
                return $block_content;
            }
        } catch (\Exception $e) {
            Logger::error('ProductGalleryIntegration: Error rendering layout in block filter', [
                'error' => $e->getMessage(),
                'layout_id' => self::$layoutId,
            ]);
            return $block_content;
        }
    }
}

