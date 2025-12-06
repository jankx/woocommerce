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
            
            // Filter block render (Gutenberg blocks)
            // Use priority 20 to ensure block has finished rendering
            add_filter('render_block', [$this, 'transformBlockOutput'], 20, 2);
            
            // Filter widget output (Sidebar widgets)
            add_filter('woocommerce_product_categories_list_html', [$this, 'transformWidgetOutput'], 10, 2);
            
            // Filter wp_list_categories output (if used by theme)
            add_filter('wp_list_categories', [$this, 'transformCategoryList'], 10, 2);
            
            // Filter final HTML output để catch category lists
            add_filter('the_content', [$this, 'transformContentOutput'], 999);
            
            // Inject JavaScript để transform HTML after page load (fallback)
            add_action('wp_footer', [$this, 'injectTransformScript'], 999);
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
        Logger::debug('CategoryBlockFilterHook: Checking accordion enable - filter', [
            'enabled' => $enabled,
        ]);
        
        // Check via theme option
        if (!$enabled) {
            $option = get_option('jankx_woocommerce_options', []);
            $enabled = $option['product_category_block']['use_accordion'] ?? false;
            Logger::debug('CategoryBlockFilterHook: Checking accordion enable - theme option', [
                'enabled' => $enabled,
                'option_exists' => !empty($option),
            ]);
        }

        // Check via config
        if (!$enabled) {
            // Try via app() container first
            $hasApp = function_exists('app');
            $configBound = $hasApp && app()->bound('woocommerce.layout.config');
            
            if ($configBound) {
                $config = app('woocommerce.layout.config');
                $enabled = $config->get('product_category_block.use_accordion', false);
                
                Logger::debug('CategoryBlockFilterHook: Config value retrieved via app()', [
                    'enabled' => $enabled,
                    'config_path' => 'product_category_block.use_accordion',
                ]);
            } else {
                // Fallback: Load config directly from file
                $configPath = get_template_directory() . '/config/woocomerce.php';
                if (file_exists($configPath)) {
                    $config = include $configPath;
                    $enabled = $config['product_category_block']['use_accordion'] ?? false;
                    
                    Logger::debug('CategoryBlockFilterHook: Config value retrieved from file', [
                        'enabled' => $enabled,
                        'config_path' => $configPath,
                        'file_exists' => true,
                    ]);
                } else {
                    Logger::warning('CategoryBlockFilterHook: Config file not found', [
                        'has_app' => $hasApp,
                        'config_bound' => $configBound,
                        'config_path' => $configPath,
                    ]);
                }
            }
        }

        Logger::info('CategoryBlockFilterHook: Accordion enable check result', [
            'enabled' => $enabled,
        ]);

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
        // CRITICAL: Only transform AFTER block has rendered HTML
        // Don't interfere with WooCommerce block's internal rendering process
        if (empty($block_content) || strlen(trim($block_content)) === 0) {
            // Block chưa render xong, return để không can thiệp
            return $block_content;
        }

        // Process WooCommerce product categories block
        $isWooCommerceBlock = isset($block['blockName']) && $block['blockName'] === 'woocommerce/product-categories';
        
        // Process WordPress Categories block if it's showing product categories
        $isWordPressCategoriesBlock = isset($block['blockName']) && 
            ($block['blockName'] === 'core/categories' || $block['blockName'] === 'core/category');
        
        // Check if WordPress Categories block is showing product_cat taxonomy
        if ($isWordPressCategoriesBlock) {
            $taxonomy = $block['attrs']['taxonomy'] ?? 'category';
            if ($taxonomy !== 'product_cat') {
                return $block_content; // Not product categories, skip
            }
        }
        
        // Check if HTML contains product category classes (fallback check)
        if (!$isWooCommerceBlock && !$isWordPressCategoriesBlock) {
            // Check if HTML contains product category structure
            $hasCatItem = strpos($block_content, 'cat-item-') !== false;
            $hasWpBlockCategories = strpos($block_content, 'wp-block-categories-list') !== false;
            
            if (!$hasCatItem && !$hasWpBlockCategories) {
                return $block_content;
            }
        }

        // Only transform if we have actual HTML content
        if (strpos($block_content, '<ul') === false && strpos($block_content, '<li') === false) {
            // No HTML structure yet, block still rendering
            return $block_content;
        }

        Logger::debug('CategoryBlockFilterHook: Transforming categories block', [
            'block_name' => $block['blockName'] ?? 'unknown',
            'content_length' => strlen($block_content),
            'is_woocommerce' => $isWooCommerceBlock,
            'is_wp_categories' => $isWordPressCategoriesBlock,
        ]);

        // Parse HTML và extract categories
        $categories = $this->parseHtmlToCategories($block_content);
        
        if (empty($categories)) {
            Logger::warning('CategoryBlockFilterHook: No categories found in HTML', [
                'content_preview' => substr($block_content, 0, 200),
            ]);
            return $block_content; // Return original content
        }

        // Get layout
        $layoutId = apply_filters('jankx_woocommerce_category_block_layout', 'expand-collapse-category');
        $layout = $this->layoutManager->get($layoutId);
        
        if (!$layout) {
            Logger::warning('CategoryBlockFilterHook: Layout not found', [
                'layout_id' => $layoutId,
            ]);
            return $block_content; // Return original content
        }

        Logger::info('CategoryBlockFilterHook: Rendering with accordion layout', [
            'layout_id' => $layoutId,
            'categories_count' => count($categories),
        ]);

        try {
            // Render với custom layout
            if (method_exists($layout, 'renderNestedCategories')) {
                $rendered = $layout->renderNestedCategories($categories, $block['attrs'] ?? []);
                if (!empty($rendered)) {
                    return $rendered;
                }
            }

            $rendered = $layout->render([
                'categories' => $this->flattenCategories($categories),
                'options' => $block['attrs'] ?? [],
            ]);
            
            // Ensure we return valid HTML
            if (empty($rendered) || !is_string($rendered)) {
                Logger::warning('CategoryBlockFilterHook: Layout render returned empty or invalid', [
                    'rendered_type' => gettype($rendered),
                ]);
                return $block_content; // Return original content
            }
            
            return $rendered;
        } catch (\Exception $e) {
            Logger::error('CategoryBlockFilterHook: Error rendering layout', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $block_content; // Return original content on error
        }
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
     * Transform widget output
     *
     * @param string $html
     * @param array $args
     * @return string
     */
    public function transformWidgetOutput(string $html, array $args = []): string
    {
        Logger::debug('CategoryBlockFilterHook: Transforming widget output', [
            'html_length' => strlen($html),
            'args' => $args,
        ]);

        // Parse HTML và extract categories
        $categories = $this->parseHtmlToCategories($html);
        
        if (empty($categories)) {
            Logger::warning('CategoryBlockFilterHook: No categories found in widget HTML');
            return $html;
        }

        // Get layout
        $layoutId = apply_filters('jankx_woocommerce_category_block_layout', 'expand-collapse-category');
        $layout = $this->layoutManager->get($layoutId);
        
        if (!$layout) {
            Logger::warning('CategoryBlockFilterHook: Layout not found for widget', [
                'layout_id' => $layoutId,
            ]);
            return $html;
        }

        Logger::info('CategoryBlockFilterHook: Rendering widget with accordion layout', [
            'layout_id' => $layoutId,
            'categories_count' => count($categories),
        ]);

        // Render với custom layout
        if (method_exists($layout, 'renderNestedCategories')) {
            return $layout->renderNestedCategories($categories, $args);
        }

        return $layout->render([
            'categories' => $this->flattenCategories($categories),
            'options' => $args,
        ]);
    }

    /**
     * Transform wp_list_categories output
     *
     * @param string $output
     * @param array $args
     * @return string
     */
    public function transformCategoryList(string $output, array $args = []): string
    {
        // Only transform if it's product categories
        if (!isset($args['taxonomy']) || $args['taxonomy'] !== 'product_cat') {
            return $output;
        }

        Logger::debug('CategoryBlockFilterHook: Transforming wp_list_categories output', [
            'output_length' => strlen($output),
            'taxonomy' => $args['taxonomy'] ?? 'unknown',
        ]);

        return $this->transformWidgetOutput($output, $args);
    }

    /**
     * Transform content output (catch category lists in content)
     *
     * @param string $content
     * @return string
     */
    public function transformContentOutput(string $content): string
    {
        // Check if content contains product category list
        if (strpos($content, 'wp-block-categories-list') === false || 
            strpos($content, 'cat-item-') === false) {
            return $content;
        }

        // Extract category list HTML
        preg_match_all('/<ul[^>]*class="[^"]*wp-block-categories-list[^"]*"[^>]*>.*?<\/ul>/is', $content, $matches);
        
        if (empty($matches[0])) {
            return $content;
        }

        Logger::debug('CategoryBlockFilterHook: Found category list in content', [
            'matches_count' => count($matches[0]),
        ]);

        foreach ($matches[0] as $html) {
            // Check if it's product categories (has cat-item- classes)
            if (strpos($html, 'product-category') !== false || preg_match('/cat-item-\d+/', $html)) {
                $transformed = $this->transformWidgetOutput($html);
                if ($transformed !== $html) {
                    $content = str_replace($html, $transformed, $content);
                    Logger::info('CategoryBlockFilterHook: Transformed category list in content');
                }
            }
        }

        return $content;
    }

    /**
     * Inject JavaScript để transform HTML after page load (fallback)
     *
     * @return void
     */
    public function injectTransformScript(): void
    {
        // Get layout để render script
        $layoutId = apply_filters('jankx_woocommerce_category_block_layout', 'expand-collapse-category');
        $layout = $this->layoutManager->get($layoutId);
        
        if (!$layout) {
            return;
        }

        Logger::debug('CategoryBlockFilterHook: Injecting transform script', [
            'layout_id' => $layoutId,
        ]);

        // Check if category list exists on page
        ?>
        <script>
        (function() {
            'use strict';
            
            // Wait for DOM ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', transformCategoryLists);
            } else {
                transformCategoryLists();
            }
            
            function transformCategoryLists() {
                // Find all category lists
                const categoryLists = document.querySelectorAll('ul.wp-block-categories-list, ul.wp-block-categories');
                
                if (categoryLists.length === 0) {
                    return;
                }
                
                console.log('[Jankx WooCommerce] Found', categoryLists.length, 'category list(s) to transform');
                
                categoryLists.forEach(function(list) {
                    // Check if already transformed
                    if (list.closest('.jankx-categories-expand-collapse')) {
                        return;
                    }
                    
                    // Check if it's product categories (has cat-item- classes)
                    const hasProductCategories = list.querySelectorAll('li[class*="cat-item-"]').length > 0;
                    if (!hasProductCategories) {
                        return;
                    }
                    
                    // Transform to accordion
                    transformToAccordion(list);
                });
            }
            
            function transformToAccordion(list) {
                // Create accordion wrapper
                const wrapper = document.createElement('div');
                wrapper.className = 'jankx-categories-expand-collapse';
                
                // Wrap list
                list.parentNode.insertBefore(wrapper, list);
                wrapper.appendChild(list);
                
                // Transform each category item
                const items = list.querySelectorAll('li.cat-item');
                items.forEach(function(item) {
                    const link = item.querySelector('a');
                    if (!link) return;
                    
                    const categoryName = link.textContent.trim();
                    const categoryUrl = link.getAttribute('href');
                    const hasChildren = item.querySelector('ul.children') !== null;
                    
                    // Create accordion item
                    const accordionItem = document.createElement('div');
                    accordionItem.className = 'category-accordion-item';
                    
                    // Create header
                    const header = document.createElement('div');
                    header.className = 'category-header';
                    
                    const main = document.createElement('div');
                    main.className = 'category-main';
                    
                    const info = document.createElement('div');
                    info.className = 'category-info';
                    
                    const title = document.createElement('a');
                    title.href = categoryUrl;
                    title.className = 'category-title';
                    title.textContent = categoryName;
                    
                    info.appendChild(title);
                    main.appendChild(info);
                    header.appendChild(main);
                    
                    // Add toggle button if has children
                    if (hasChildren) {
                        const toggle = document.createElement('button');
                        toggle.className = 'category-toggle';
                        toggle.innerHTML = '<span class="toggle-icon"><span class="icon-expand">+</span><span class="icon-collapse">-</span></span>';
                        
                        const content = item.querySelector('ul.children');
                        if (content) {
                            content.style.display = 'none';
                            content.className = 'category-content';
                            
                            toggle.addEventListener('click', function(e) {
                                e.preventDefault();
                                const isExpanded = content.style.display !== 'none';
                                content.style.display = isExpanded ? 'none' : 'block';
                                toggle.classList.toggle('expanded', !isExpanded);
                            });
                        }
                        
                        header.appendChild(toggle);
                    }
                    
                    accordionItem.appendChild(header);
                    if (hasChildren) {
                        const content = item.querySelector('ul.children');
                        if (content) {
                            accordionItem.appendChild(content);
                        }
                    }
                    
                    // Replace original item
                    item.parentNode.replaceChild(accordionItem, item);
                });
                
                console.log('[Jankx WooCommerce] Category list transformed to accordion');
            }
        })();
        </script>
        <?php
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

