<?php

namespace Jankx\WooCommerce\Integrations;

use Jankx\WooCommerce\Helpers\Logger;
use Jankx\WooCommerce\LayoutSystem\LayoutManager;

/**
 * Class ProductCategoryBlockIntegration
 * 
 * Integration để apply Expand/Collapse Layout cho WooCommerce Product Categories block
 * 
 * Usage:
 * ProductCategoryBlockIntegration::enable();
 * ProductCategoryBlockIntegration::useLayout('expand-collapse-category');
 */
class ProductCategoryBlockIntegration
{
    /**
     * @var bool Enabled status
     */
    private static $enabled = false;

    /**
     * @var string Layout ID to use
     */
    private static $layoutId = 'expand-collapse-category';

    /**
     * @var LayoutManager
     */
    private static $layoutManager;

    /**
     * Enable integration
     *
     * @param string $layoutId Layout ID to use (default: expand-collapse-category)
     * @return void
     */
    public static function enable(string $layoutId = 'expand-collapse-category'): void
    {
        self::$enabled = true;
        self::$layoutId = $layoutId;
        self::$layoutManager = LayoutManager::getInstance();

        Logger::info('ProductCategoryBlockIntegration: Enabled', [
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
        Logger::info('ProductCategoryBlockIntegration: Disabled');
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
        
        Logger::debug('ProductCategoryBlockIntegration: Layout changed', [
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
        // Filter render_block để transform WooCommerce block
        add_filter('render_block', [__CLASS__, 'filterBlockOutput'], 10, 2);
        
        // Filter để enable accordion
        add_filter('jankx_woocommerce_category_block_use_accordion', '__return_true');
        
        // Filter để set layout
        add_filter('jankx_woocommerce_category_block_layout', function() {
            return self::$layoutId;
        });
    }

    /**
     * Filter block output
     *
     * @param string $block_content
     * @param array $block
     * @return string
     */
    public static function filterBlockOutput(string $block_content, array $block): string
    {
        // Only process WooCommerce product categories block
        if (!isset($block['blockName']) || $block['blockName'] !== 'woocommerce/product-categories') {
            return $block_content;
        }

        if (!self::$enabled) {
            return $block_content;
        }

        Logger::debug('ProductCategoryBlockIntegration: Processing block', [
            'block_name' => $block['blockName'],
        ]);

        // Get layout
        $layout = self::$layoutManager->get(self::$layoutId);
        
        if (!$layout) {
            Logger::warning('ProductCategoryBlockIntegration: Layout not found', [
                'layout_id' => self::$layoutId,
            ]);
            return $block_content;
        }

        // Parse HTML to extract categories
        $categories = self::parseBlockHtml($block_content);
        
        if (empty($categories)) {
            Logger::warning('ProductCategoryBlockIntegration: No categories found');
            return $block_content;
        }

        Logger::info('ProductCategoryBlockIntegration: Transforming block', [
            'categories_count' => count($categories),
            'layout_id' => self::$layoutId,
        ]);

        // Render with layout
        if (method_exists($layout, 'renderNestedCategories')) {
            return $layout->renderNestedCategories($categories, $block['attrs'] ?? []);
        }

        // Fallback: flatten và render
        $terms = self::flattenToTerms($categories);
        return $layout->render([
            'categories' => $terms,
            'options' => $block['attrs'] ?? [],
        ]);
    }

    /**
     * Parse block HTML to category structure
     *
     * @param string $html
     * @return array
     */
    private static function parseBlockHtml(string $html): array
    {
        $doc = new \DOMDocument();
        @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        $rootUl = $doc->getElementsByTagName('ul')->item(0);
        
        if (!$rootUl) {
            return [];
        }

        $categories = [];
        
        foreach ($rootUl->childNodes as $li) {
            if ($li->nodeType !== XML_ELEMENT_NODE || $li->nodeName !== 'li') {
                continue;
            }

            $categoryData = self::parseCategoryItem($li);
            if ($categoryData) {
                $categories[] = $categoryData;
            }
        }

        return $categories;
    }

    /**
     * Parse category item recursively
     *
     * @param \DOMElement $li
     * @return array|null
     */
    private static function parseCategoryItem(\DOMElement $li): ?array
    {
        $data = [];

        // Extract category ID
        $classes = $li->getAttribute('class');
        preg_match('/cat-item-(\d+)/', $classes, $matches);
        
        if (empty($matches[1])) {
            return null;
        }

        $data['id'] = intval($matches[1]);
        $data['classes'] = explode(' ', $classes);
        $data['is_current'] = strpos($classes, 'current-cat') !== false;
        $data['is_current_ancestor'] = strpos($classes, 'current-cat-ancestor') !== false;

        // Extract link
        $links = $li->getElementsByTagName('a');
        if ($links->length > 0) {
            $link = $links->item(0);
            $data['url'] = $link->getAttribute('href');
            $data['name'] = trim($link->textContent);
        }

        // Parse children recursively
        $data['children'] = [];
        foreach ($li->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE && $child->nodeName === 'ul') {
                foreach ($child->childNodes as $childLi) {
                    if ($childLi->nodeType === XML_ELEMENT_NODE && $childLi->nodeName === 'li') {
                        $childData = self::parseCategoryItem($childLi);
                        if ($childData) {
                            $data['children'][] = $childData;
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Flatten categories to WP_Term objects
     *
     * @param array $categories
     * @return array
     */
    private static function flattenToTerms(array $categories): array
    {
        $terms = [];
        
        foreach ($categories as $categoryData) {
            $term = get_term($categoryData['id'], 'product_cat');
            if ($term && !is_wp_error($term)) {
                $terms[] = $term;
            }
            
            // Add children
            if (!empty($categoryData['children'])) {
                $terms = array_merge($terms, self::flattenToTerms($categoryData['children']));
            }
        }

        return $terms;
    }
}

