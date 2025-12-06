<?php

namespace Jankx\WooCommerce\Layouts\CategoryBlock\Filters;

/**
 * Class CategoryBlockFilter
 * 
 * Filter WooCommerce Product Categories block output
 * Transform nested list HTML thành accordion format
 */
class CategoryBlockFilter
{
    /**
     * @var string Current layout to apply
     */
    private $layoutId = '';

    /**
     * @var \Jankx\WooCommerce\LayoutSystem\LayoutManager
     */
    private $layoutManager;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->layoutManager = \Jankx\WooCommerce\LayoutSystem\LayoutManager::getInstance();
        $this->init();
    }

    /**
     * Initialize filters
     *
     * @return void
     */
    private function init(): void
    {
        // Filter WooCommerce block content
        add_filter('render_block', [$this, 'filterBlockContent'], 10, 2);
        
        // Filter category list output
        add_filter('woocommerce_product_categories_list_html', [$this, 'filterCategoryListHtml'], 10, 2);
    }

    /**
     * Filter block content
     *
     * @param string $block_content
     * @param array $block
     * @return string
     */
    public function filterBlockContent(string $block_content, array $block): string
    {
        // Check if it's WooCommerce product categories block
        if (!isset($block['blockName']) || $block['blockName'] !== 'woocommerce/product-categories') {
            return $block_content;
        }

        \Jankx\WooCommerce\Helpers\Logger::debug('CategoryBlockFilter: Processing WooCommerce product categories block');

        // Get current layout setting
        $useAccordion = apply_filters('jankx_woocommerce_use_accordion_category', false);
        
        if (!$useAccordion) {
            return $block_content;
        }

        // Transform HTML
        return $this->transformToAccordion($block_content, $block);
    }

    /**
     * Filter category list HTML
     *
     * @param string $html
     * @param array $args
     * @return string
     */
    public function filterCategoryListHtml(string $html, array $args): string
    {
        $useAccordion = apply_filters('jankx_woocommerce_use_accordion_category', false);
        
        if (!$useAccordion) {
            return $html;
        }

        return $this->transformToAccordion($html, $args);
    }

    /**
     * Transform nested list HTML thành accordion
     *
     * @param string $html
     * @param array $context
     * @return string
     */
    public function transformToAccordion(string $html, array $context = []): string
    {
        \Jankx\WooCommerce\Helpers\Logger::debug('CategoryBlockFilter: Transforming HTML to accordion');

        // Parse HTML
        $doc = new \DOMDocument();
        @$doc->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        // Get root UL
        $rootUl = $doc->getElementsByTagName('ul')->item(0);
        
        if (!$rootUl) {
            return $html;
        }

        // Get layout
        $layoutId = $context['layout'] ?? 'expand-collapse-category';
        $layout = $this->layoutManager->get($layoutId);
        
        if (!$layout) {
            \Jankx\WooCommerce\Helpers\Logger::warning('CategoryBlockFilter: Layout not found', [
                'layout_id' => $layoutId,
            ]);
            return $html;
        }

        // Extract categories from HTML
        $categories = $this->extractCategoriesFromHtml($rootUl);
        
        \Jankx\WooCommerce\Helpers\Logger::info('CategoryBlockFilter: Categories extracted', [
            'count' => count($categories),
        ]);

        // Render với layout
        return $layout->render([
            'categories' => $categories,
            'options' => $context,
        ]);
    }

    /**
     * Extract categories từ nested HTML list
     *
     * @param \DOMElement $ul
     * @return array
     */
    private function extractCategoriesFromHtml(\DOMElement $ul): array
    {
        $categories = [];
        
        foreach ($ul->childNodes as $li) {
            if ($li->nodeType !== XML_ELEMENT_NODE || $li->nodeName !== 'li') {
                continue;
            }

            // Get category ID từ class
            $classes = $li->getAttribute('class');
            preg_match('/cat-item-(\d+)/', $classes, $matches);
            
            if (empty($matches[1])) {
                continue;
            }

            $categoryId = intval($matches[1]);
            
            // Get term object
            $term = get_term($categoryId, 'product_cat');
            
            if ($term && !is_wp_error($term)) {
                $categories[] = $term;
            }
        }

        return $categories;
    }

    /**
     * Parse nested categories recursively
     *
     * @param \DOMElement $li
     * @return array|null
     */
    private function parseCategoryItem(\DOMElement $li): ?array
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

        // Extract link and name
        $links = $li->getElementsByTagName('a');
        if ($links->length > 0) {
            $link = $links->item(0);
            $data['url'] = $link->getAttribute('href');
            $data['name'] = trim($link->textContent);
            $data['is_current'] = strpos($link->getAttribute('aria-current'), 'page') !== false;
        }

        // Extract children
        $childUl = null;
        foreach ($li->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE && $child->nodeName === 'ul') {
                $childUl = $child;
                break;
            }
        }

        if ($childUl) {
            $data['children'] = [];
            foreach ($childUl->childNodes as $childLi) {
                if ($childLi->nodeType === XML_ELEMENT_NODE && $childLi->nodeName === 'li') {
                    $childData = $this->parseCategoryItem($childLi);
                    if ($childData) {
                        $data['children'][] = $childData;
                    }
                }
            }
        }

        return $data;
    }
}

