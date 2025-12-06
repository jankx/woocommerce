<?php

namespace Jankx\WooCommerce\Hooks;

use Jankx\WooCommerce\Helpers\Logger;

/**
 * Class CategoryBlockFilterHook
 * 
 * Hook vào WooCommerce Product Categories block để apply custom layout
 */
class CategoryBlockFilterHook
{
    /**
     * @var \Jankx\WooCommerce\LayoutSystem\LayoutManager
     */
    private $layoutManager;

    /**
     * @var bool Enable accordion transform
     */
    private $enableAccordion = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->layoutManager = \Jankx\WooCommerce\LayoutSystem\LayoutManager::getInstance();
        $this->init();
    }

    /**
     * Initialize hooks
     *
     * @return void
     */
    private function init(): void
    {
        // Check if accordion should be enabled
        $this->enableAccordion = $this->shouldEnableAccordion();

        if ($this->enableAccordion) {
            Logger::info('CategoryBlockFilterHook: Accordion mode enabled');
            
            // Filter block render
            add_filter('render_block', [$this, 'transformBlockOutput'], 10, 2);
        }
    }

    /**
     * Check if accordion should be enabled
     *
     * @return bool
     */
    private function shouldEnableAccordion(): bool
    {
        // Check via filter
        $enabled = apply_filters('jankx_woocommerce_category_block_use_accordion', false);
        
        // Check via theme option
        if (!$enabled) {
            $option = get_option('jankx_woocommerce_options', []);
            $enabled = $option['product_category_block']['use_accordion'] ?? false;
        }

        // Check via config
        if (!$enabled && function_exists('app') && app()->bound('woocommerce.layout.config')) {
            $config = app('woocommerce.layout.config');
            $enabled = $config->get('product_category_block.use_accordion', false);
        }

        return $enabled;
    }

    /**
     * Transform block output
     *
     * @param string $block_content
     * @param array $block
     * @return string
     */
    public function transformBlockOutput(string $block_content, array $block): string
    {
        // Only process WooCommerce product categories block
        if (!isset($block['blockName']) || $block['blockName'] !== 'woocommerce/product-categories') {
            return $block_content;
        }

        Logger::debug('CategoryBlockFilterHook: Transforming product categories block', [
            'block_name' => $block['blockName'],
            'content_length' => strlen($block_content),
        ]);

        // Parse HTML và extract categories
        $categories = $this->parseHtmlToCategories($block_content);
        
        if (empty($categories)) {
            Logger::warning('CategoryBlockFilterHook: No categories found in HTML');
            return $block_content;
        }

        // Get layout
        $layoutId = apply_filters('jankx_woocommerce_category_block_layout', 'expand-collapse-category');
        $layout = $this->layoutManager->get($layoutId);
        
        if (!$layout) {
            Logger::warning('CategoryBlockFilterHook: Layout not found', [
                'layout_id' => $layoutId,
            ]);
            return $block_content;
        }

        Logger::info('CategoryBlockFilterHook: Rendering with accordion layout', [
            'layout_id' => $layoutId,
            'categories_count' => count($categories),
        ]);

        // Render với custom layout
        if (method_exists($layout, 'renderNestedCategories')) {
            return $layout->renderNestedCategories($categories, $block['attrs'] ?? []);
        }

        return $layout->render([
            'categories' => $this->flattenCategories($categories),
            'options' => $block['attrs'] ?? [],
        ]);
    }

    /**
     * Parse HTML list thành category data structure
     *
     * @param string $html
     * @return array
     */
    private function parseHtmlToCategories(string $html): array
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

            $categoryData = $this->parseCategoryItem($li);
            if ($categoryData) {
                $categories[] = $categoryData;
            }
        }

        return $categories;
    }

    /**
     * Parse single category item (recursive)
     *
     * @param \DOMElement $li
     * @return array|null
     */
    private function parseCategoryItem(\DOMElement $li): ?array
    {
        $data = [];

        // Extract category ID từ class
        $classes = $li->getAttribute('class');
        preg_match('/cat-item-(\d+)/', $classes, $matches);
        
        if (empty($matches[1])) {
            return null;
        }

        $data['id'] = intval($matches[1]);
        $data['classes'] = explode(' ', $classes);
        $data['is_current'] = strpos($classes, 'current-cat') !== false;
        $data['is_current_ancestor'] = strpos($classes, 'current-cat-ancestor') !== false;

        // Extract link info
        $links = $li->getElementsByTagName('a');
        if ($links->length > 0) {
            $link = $links->item(0);
            $data['url'] = $link->getAttribute('href');
            $data['name'] = trim($link->textContent);
            $data['aria_current'] = $link->getAttribute('aria-current');
        }

        // Parse children recursively
        $data['children'] = [];
        foreach ($li->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE && $child->nodeName === 'ul') {
                foreach ($child->childNodes as $childLi) {
                    if ($childLi->nodeType === XML_ELEMENT_NODE && $childLi->nodeName === 'li') {
                        $childData = $this->parseCategoryItem($childLi);
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
     * Flatten nested categories (nếu layout không support nested)
     *
     * @param array $categories
     * @return array
     */
    private function flattenCategories(array $categories): array
    {
        $flattened = [];
        
        foreach ($categories as $categoryData) {
            $term = get_term($categoryData['id'], 'product_cat');
            if ($term && !is_wp_error($term)) {
                $flattened[] = $term;
            }
        }

        return $flattened;
    }

    /**
     * Get instance
     *
     * @return self
     */
    public static function getInstance(): self
    {
        static $instance = null;
        
        if ($instance === null) {
            $instance = new self();
        }

        return $instance;
    }
}

