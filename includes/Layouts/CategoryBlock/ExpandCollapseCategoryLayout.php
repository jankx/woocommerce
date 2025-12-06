<?php

namespace Jankx\WooCommerce\Layouts\CategoryBlock;

use Jankx\WooCommerce\Abstracts\Layouts\AbstractProductCategoryBlockLayout;

/**
 * Class ExpandCollapseCategoryLayout
 * 
 * Modern category layout với expand/collapse functionality
 * Categories có thể expand để show subcategories hoặc products
 */
class ExpandCollapseCategoryLayout extends AbstractProductCategoryBlockLayout
{
    /**
     * Show subcategories
     *
     * @var bool
     */
    protected $showSubcategories = true;

    /**
     * Show product preview
     *
     * @var bool
     */
    protected $showProductPreview = true;

    /**
     * Default expanded
     *
     * @var bool
     */
    protected $defaultExpanded = false;

    /**
     * Animation speed (ms)
     *
     * @var int
     */
    protected $animationSpeed = 300;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(
            'expand-collapse-category',
            __('Expand/Collapse Layout', 'jankx-woocommerce'),
            'product-category-block'
        );

        $this->displayType = 'accordion';
        $this->showEmptyCategories = false;
        $this->priority = 15;

        $this->supportedSettings = [
            'columns',
            'show_subcategories',
            'show_product_preview',
            'default_expanded',
            'animation_speed',
            'accordion_style', // minimal, bordered, card
            'icon_style', // plus-minus, arrow, chevron
            'maintain_hierarchy', // Maintain nested structure
            'max_depth', // Maximum depth to display
        ];
    }

    /**
     * Render từ nested category data (từ HTML parse)
     *
     * @param array $categoryData Parsed category data với children
     * @param int $depth Current depth
     * @return string
     */
    public function renderNestedCategory(array $categoryData, int $depth = 0): string
    {
        $categoryId = $categoryData['id'];
        $term = get_term($categoryId, 'product_cat');
        
        if (!$term || is_wp_error($term)) {
            return '';
        }

        $hasChildren = !empty($categoryData['children']);
        $isCurrent = $categoryData['is_current'] ?? false;
        $expandedClass = ($this->defaultExpanded || $isCurrent) ? 'is-expanded' : '';
        $depthClass = 'depth-' . $depth;
        
        ob_start();
        ?>
        <div class="category-accordion-item <?php echo esc_attr($expandedClass . ' ' . $depthClass); ?>" 
             data-category-id="<?php echo esc_attr($term->term_id); ?>"
             data-depth="<?php echo esc_attr($depth); ?>">
            <div class="category-header">
                <div class="category-main">
                    <?php echo $this->renderCategoryThumbnail($term); ?>
                    <div class="category-info">
                        <?php echo $this->renderCategoryTitle($term); ?>
                        <?php echo $this->renderProductCount($term); ?>
                    </div>
                </div>
                
                <?php if ($hasChildren || $this->showProductPreview): ?>
                <button class="category-toggle" aria-label="<?php esc_attr_e('Expand/Collapse', 'jankx-woocommerce'); ?>">
                    <span class="toggle-icon">
                        <span class="icon-expand">+</span>
                        <span class="icon-collapse">-</span>
                    </span>
                </button>
                <?php endif; ?>
            </div>

            <?php if ($hasChildren || $this->showProductPreview): ?>
            <div class="category-content" style="<?php echo ($this->defaultExpanded || $isCurrent) ? '' : 'display: none;'; ?>">
                <?php 
                // Render children categories (subcategories)
                if ($hasChildren) {
                    echo '<div class="nested-categories">';
                    foreach ($categoryData['children'] as $childData) {
                        echo $this->renderNestedCategory($childData, $depth + 1);
                    }
                    echo '</div>';
                }
                
                // Render product preview (only for leaf categories or level 0)
                if ($this->showProductPreview && ($depth === 0 || !$hasChildren)) {
                    echo $this->renderProductPreview($term);
                }
                ?>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render categories từ nested data structure
     *
     * @param array $nestedData Array of category data với children
     * @param array $options
     * @return string
     */
    public function renderNestedCategories(array $nestedData, array $options = []): string
    {
        ob_start();
        echo '<div class="jankx-categories-expand-collapse nested-structure" data-animation-speed="' . esc_attr($this->animationSpeed) . '">';
        
        foreach ($nestedData as $categoryData) {
            echo $this->renderNestedCategory($categoryData, 0);
        }
        
        echo '</div>';
        
        // Add inline JavaScript
        echo $this->renderScript();
        
        $output = ob_get_clean();
        
        // Add fingerprint for debugging
        return $this->wrapWithFingerprint($output);
    }

    /**
     * {@inheritDoc}
     */
    public function renderCategories(array $categories, array $options = []): string
    {
        $template = $this->getTemplatePath();

        ob_start();
        echo '<div class="jankx-categories-expand-collapse" data-animation-speed="' . esc_attr($this->animationSpeed) . '">';
        
        foreach ($categories as $category) {
            if ($category instanceof \WP_Term) {
                echo $this->renderCategory($category);
            }
        }
        
        echo '</div>';
        
        // Add inline JavaScript
        echo $this->renderScript();
        
        $output = ob_get_clean();
        
        // Add fingerprint for debugging
        return $this->wrapWithFingerprint($output);
    }

    /**
     * {@inheritDoc}
     */
    public function renderCategory(\WP_Term $category): string
    {
        $hasChildren = $this->hasSubcategories($category);
        $expandedClass = $this->defaultExpanded ? 'is-expanded' : '';
        
        ob_start();
        ?>
        <div class="category-accordion-item <?php echo esc_attr($expandedClass); ?>" data-category-id="<?php echo esc_attr($category->term_id); ?>">
            <div class="category-header">
                <div class="category-main">
                    <?php echo $this->renderCategoryThumbnail($category); ?>
                    <div class="category-info">
                        <?php echo $this->renderCategoryTitle($category); ?>
                        <?php echo $this->renderProductCount($category); ?>
                    </div>
                </div>
                
                <?php if ($hasChildren || $this->showProductPreview): ?>
                <button class="category-toggle" aria-label="<?php esc_attr_e('Expand/Collapse', 'jankx-woocommerce'); ?>">
                    <span class="toggle-icon">
                        <span class="icon-expand">+</span>
                        <span class="icon-collapse">-</span>
                    </span>
                </button>
                <?php endif; ?>
            </div>

            <?php if ($hasChildren || $this->showProductPreview): ?>
            <div class="category-content" style="<?php echo $this->defaultExpanded ? '' : 'display: none;'; ?>">
                <?php 
                if ($this->showSubcategories && $hasChildren) {
                    echo $this->renderSubcategories($category);
                }
                
                if ($this->showProductPreview) {
                    echo $this->renderProductPreview($category);
                }
                ?>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Check if category has subcategories
     *
     * @param \WP_Term $category
     * @return bool
     */
    protected function hasSubcategories(\WP_Term $category): bool
    {
        if (!function_exists('get_term_children')) {
            return false;
        }
        
        $children = get_term_children($category->term_id, 'product_cat');
        return !empty($children) && !is_wp_error($children);
    }

    /**
     * Render subcategories
     *
     * @param \WP_Term $category
     * @return string
     */
    protected function renderSubcategories(\WP_Term $category): string
    {
        if (!function_exists('get_terms')) {
            return '';
        }
        
        $children = get_terms([
            'taxonomy' => 'product_cat',
            'parent' => $category->term_id,
            'hide_empty' => !$this->showEmptyCategories,
        ]);

        if (empty($children) || is_wp_error($children)) {
            return '';
        }

        ob_start();
        echo '<div class="subcategories-list">';
        echo '<ul class="subcategories">';
        
        foreach ($children as $child) {
            $hasChildSubcategories = $this->hasSubcategories($child);
            
            if ($hasChildSubcategories) {
                // Render nested subcategories
                $childChildren = get_terms([
                    'taxonomy' => 'product_cat',
                    'parent' => $child->term_id,
                    'hide_empty' => !$this->showEmptyCategories,
                ]);
                
                printf(
                    '<li class="subcategory-item has-children">
                        <div class="subcategory-header">
                            <a href="%s" class="subcategory-link">%s</a>
                            <button class="category-toggle" aria-label="Toggle"><span class="toggle-icon"><span class="icon-expand">▼</span><span class="icon-collapse">▲</span></span></button>
                        </div>
                        <div class="subcategory-content" style="display: none;">
                            <ul class="subcategories-nested">',
                    esc_url(get_term_link($child)),
                    esc_html($child->name)
                );
                
                foreach ($childChildren as $grandchild) {
                    printf(
                        '<li><a href="%s" class="subcategory-link">%s</a></li>',
                        esc_url(get_term_link($grandchild)),
                        esc_html($grandchild->name)
                    );
                }
                
                echo '</ul></div></li>';
            } else {
                printf(
                    '<li class="subcategory-item"><a href="%s" class="subcategory-link">%s</a></li>',
                    esc_url(get_term_link($child)),
                    esc_html($child->name)
                );
            }
        }
        
        echo '</ul>';
        echo '</div>';
        return ob_get_clean();
    }

    /**
     * Render product preview
     *
     * @param \WP_Term $category
     * @return string
     */
    protected function renderProductPreview(\WP_Term $category): string
    {
        if (!function_exists('wc_get_products')) {
            return '';
        }
        
        // Get top products from category
        $products = wc_get_products([
            'category' => [$category->slug],
            'limit' => 4,
            'orderby' => 'popularity',
            'order' => 'DESC',
        ]);

        if (empty($products)) {
            return '';
        }

        ob_start();
        echo '<div class="products-preview">';
        echo '<h4 class="products-preview-title">' . __('Featured Products', 'jankx-woocommerce') . '</h4>';
        echo '<div class="products-preview-grid">';
        
        foreach ($products as $product) {
            ?>
            <div class="product-preview-item">
                <a href="<?php echo esc_url($product->get_permalink()); ?>" class="product-preview-link">
                    <?php echo $product->get_image('thumbnail'); ?>
                    <span class="product-name"><?php echo esc_html($product->get_name()); ?></span>
                    <span class="product-price"><?php echo $product->get_price_html(); ?></span>
                </a>
            </div>
            <?php
        }
        
        echo '</div>';
        
        // View all link
        printf(
            '<a href="%s" class="view-all-link">%s &rarr;</a>',
            esc_url(get_term_link($category)),
            sprintf(__('View all %s', 'jankx-woocommerce'), $category->name)
        );
        
        echo '</div>';
        return ob_get_clean();
    }

    /**
     * Render JavaScript for expand/collapse
     *
     * @return string
     */
    protected function renderScript(): string
    {
        ob_start();
        ?>
        <script>
        (function() {
            'use strict';
            
            document.addEventListener('DOMContentLoaded', function() {
                const container = document.querySelector('.jankx-categories-expand-collapse');
                if (!container) return;
                
                const animationSpeed = parseInt(container.dataset.animationSpeed) || 300;
                
                // Handle toggle button clicks - support both main items and nested subcategories
                container.addEventListener('click', function(e) {
                    const toggleBtn = e.target.closest('.category-toggle');
                    if (!toggleBtn) return;
                    
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Check if it's a nested subcategory toggle
                    const subcategoryItem = toggleBtn.closest('.subcategory-item');
                    if (subcategoryItem) {
                        const content = subcategoryItem.querySelector('.subcategory-content');
                        if (!content) return;
                        
                        const isExpanded = content.style.display !== 'none';
                        if (isExpanded) {
                            content.style.display = 'none';
                            subcategoryItem.classList.remove('is-expanded');
                        } else {
                            content.style.display = 'block';
                            subcategoryItem.classList.add('is-expanded');
                        }
                        return;
                    }
                    
                    // Main category toggle
                    const item = toggleBtn.closest('.category-accordion-item');
                    const content = item.querySelector('.category-content');
                    
                    if (!content) return;
                    
                    // Toggle expanded state
                    const isExpanded = item.classList.contains('is-expanded');
                    
                    if (isExpanded) {
                        // Collapse
                        item.classList.remove('is-expanded');
                        slideUp(content, animationSpeed);
                    } else {
                        // Expand
                        item.classList.add('is-expanded');
                        slideDown(content, animationSpeed);
                    }
                });
                
                // Slide animation helpers
                function slideDown(element, duration) {
                    element.style.removeProperty('display');
                    let display = window.getComputedStyle(element).display;
                    if (display === 'none') display = 'block';
                    element.style.display = display;
                    
                    let height = element.offsetHeight;
                    element.style.overflow = 'hidden';
                    element.style.height = 0;
                    element.style.paddingTop = 0;
                    element.style.paddingBottom = 0;
                    element.style.marginTop = 0;
                    element.style.marginBottom = 0;
                    element.offsetHeight; // Force reflow
                    
                    element.style.boxSizing = 'border-box';
                    element.style.transitionProperty = 'height, margin, padding';
                    element.style.transitionDuration = duration + 'ms';
                    element.style.height = height + 'px';
                    element.style.removeProperty('padding-top');
                    element.style.removeProperty('padding-bottom');
                    element.style.removeProperty('margin-top');
                    element.style.removeProperty('margin-bottom');
                    
                    window.setTimeout(function() {
                        element.style.removeProperty('height');
                        element.style.removeProperty('overflow');
                        element.style.removeProperty('transition-duration');
                        element.style.removeProperty('transition-property');
                    }, duration);
                }
                
                function slideUp(element, duration) {
                    element.style.transitionProperty = 'height, margin, padding';
                    element.style.transitionDuration = duration + 'ms';
                    element.style.boxSizing = 'border-box';
                    element.style.height = element.offsetHeight + 'px';
                    element.offsetHeight; // Force reflow
                    
                    element.style.overflow = 'hidden';
                    element.style.height = 0;
                    element.style.paddingTop = 0;
                    element.style.paddingBottom = 0;
                    element.style.marginTop = 0;
                    element.style.marginBottom = 0;
                    
                    window.setTimeout(function() {
                        element.style.display = 'none';
                        element.style.removeProperty('height');
                        element.style.removeProperty('padding-top');
                        element.style.removeProperty('padding-bottom');
                        element.style.removeProperty('margin-top');
                        element.style.removeProperty('margin-bottom');
                        element.style.removeProperty('overflow');
                        element.style.removeProperty('transition-duration');
                        element.style.removeProperty('transition-property');
                    }, duration);
                }
            });
        })();
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    protected function generateDynamicCss(array $settings): string
    {
        $css = parent::generateDynamicCss($settings);

        // Accordion style
        if (isset($settings['accordion_style'])) {
            $style = $settings['accordion_style'];
            
            if ($style === 'minimal') {
                $css .= '
                    .category-accordion-item {
                        border: none;
                        border-bottom: 1px solid #eee;
                    }
                ';
            } elseif ($style === 'card') {
                $css .= '
                    .category-accordion-item {
                        margin-bottom: 15px;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                    }
                ';
            }
        }

        // Icon style
        if (isset($settings['icon_style'])) {
            $iconStyle = $settings['icon_style'];
            
            if ($iconStyle === 'arrow') {
                $css .= '
                    .icon-expand::before { content: "→"; }
                    .icon-collapse::before { content: "↓"; }
                ';
            } elseif ($iconStyle === 'chevron') {
                $css .= '
                    .icon-expand::before { content: "›"; font-size: 1.5em; }
                    .icon-collapse::before { content: "∨"; font-size: 1.2em; }
                ';
            }
        }

        // Animation speed
        if (isset($settings['animation_speed'])) {
            $speed = intval($settings['animation_speed']);
            $css .= sprintf(
                '.category-accordion-item .category-content { transition: all %dms ease; }',
                $speed
            );
        }

        // Show/hide options
        if (isset($settings['show_subcategories']) && !$settings['show_subcategories']) {
            $css .= '.subcategories-list { display: none; }';
        }

        if (isset($settings['show_product_preview']) && !$settings['show_product_preview']) {
            $css .= '.products-preview { display: none; }';
        }

        return $css;
    }
}

