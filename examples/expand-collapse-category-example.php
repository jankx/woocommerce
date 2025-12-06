<?php
/**
 * Example: Sử dụng Expand/Collapse Category Layout
 * 
 * Flatsome-style category accordion với expand/collapse
 */

// ============================================
// Example 1: Basic Usage
// ============================================

use Jankx\WooCommerce\LayoutSystem\LayoutManager;
use Jankx\WooCommerce\LayoutSystem\SettingsManager;

// Get layout
$layoutManager = LayoutManager::getInstance();
$layout = $layoutManager->get('expand-collapse-category');

// Get categories
$categories = get_terms([
    'taxonomy' => 'product_cat',
    'hide_empty' => true,
    'parent' => 0, // Top level only
]);

// Render
echo $layout->render([
    'categories' => $categories,
    'options' => []
]);

// ============================================
// Example 2: Customize Settings
// ============================================

$settingsManager = SettingsManager::getInstance();

// Configure layout
$settingsManager->setLayoutSettings('expand-collapse-category', [
    'show_subcategories' => true,
    'show_product_preview' => true,
    'default_expanded' => false,
    'animation_speed' => 250,
    'accordion_style' => 'card',
    'icon_style' => 'chevron',
]);

// ============================================
// Example 3: Set as Default via Filter
// ============================================

add_filter('jankx_woocommerce_current_layout', function($layout, $type) {
    if ($type === 'product-category-block') {
        $manager = LayoutManager::getInstance();
        return $manager->get('expand-collapse-category');
    }
    return $layout;
}, 10, 2);

// ============================================
// Example 4: Custom Accordion Style
// ============================================

class MyCustomAccordionLayout extends \Jankx\WooCommerce\Layouts\CategoryBlock\ExpandCollapseCategoryLayout
{
    public function __construct()
    {
        parent::__construct();
        
        // Override ID và name
        $this->id = 'my-accordion';
        $this->name = __('My Custom Accordion', 'my-theme');
        
        // Custom settings
        $this->animationSpeed = 400;
        $this->defaultExpanded = true;
        $this->showSubcategories = true;
        $this->showProductPreview = true;
    }
    
    protected function generateDynamicCss(array $settings): string
    {
        $css = parent::generateDynamicCss($settings);
        
        // Add custom CSS
        $css .= '
            .category-accordion-item {
                background: linear-gradient(to right, #f5f5f5, #fff);
            }
            .category-header:hover {
                background: linear-gradient(to right, #e8e8e8, #f5f5f5);
            }
        ';
        
        return $css;
    }
}

// Register custom layout
add_action('jankx_woocommerce_register_layouts', function($manager) {
    $manager->register(new MyCustomAccordionLayout());
});

// ============================================
// Example 5: Shortcode Integration
// ============================================

add_shortcode('category_accordion', function($atts) {
    $atts = shortcode_atts([
        'parent' => 0,
        'limit' => 10,
        'orderby' => 'name',
        'order' => 'ASC',
        'style' => 'card',
    ], $atts);
    
    // Get categories
    $categories = get_terms([
        'taxonomy' => 'product_cat',
        'parent' => $atts['parent'],
        'number' => $atts['limit'],
        'orderby' => $atts['orderby'],
        'order' => $atts['order'],
        'hide_empty' => true,
    ]);
    
    if (empty($categories) || is_wp_error($categories)) {
        return '';
    }
    
    // Get layout
    $layoutManager = LayoutManager::getInstance();
    $layout = $layoutManager->get('expand-collapse-category');
    
    if (!$layout) {
        return '';
    }
    
    // Set style
    $settingsManager = SettingsManager::getInstance();
    $settingsManager->set('expand-collapse-category.accordion_style', $atts['style']);
    
    // Render
    return $layout->render([
        'categories' => $categories,
        'options' => $atts
    ]);
});

// Usage in content:
// [category_accordion parent="0" limit="5" style="card"]

// ============================================
// Example 6: Widget Integration
// ============================================

class Category_Accordion_Widget extends \WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'category_accordion',
            __('Category Accordion', 'my-theme'),
            ['description' => __('Expandable category accordion', 'my-theme')]
        );
    }
    
    public function widget($args, $instance)
    {
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . esc_html($instance['title']) . $args['after_title'];
        }
        
        // Get categories
        $categories = get_terms([
            'taxonomy' => 'product_cat',
            'parent' => 0,
            'number' => $instance['limit'] ?? 5,
            'hide_empty' => true,
        ]);
        
        // Render with layout
        $layoutManager = LayoutManager::getInstance();
        $layout = $layoutManager->get('expand-collapse-category');
        
        if ($layout && !empty($categories)) {
            echo $layout->render(['categories' => $categories]);
        }
        
        echo $args['after_widget'];
    }
    
    public function form($instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $limit = !empty($instance['limit']) ? $instance['limit'] : 5;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php _e('Title:', 'my-theme'); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" 
                   type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('limit')); ?>">
                <?php _e('Number of categories:', 'my-theme'); ?>
            </label>
            <input class="tiny-text" id="<?php echo esc_attr($this->get_field_id('limit')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('limit')); ?>" 
                   type="number" value="<?php echo esc_attr($limit); ?>">
        </p>
        <?php
    }
}

// Register widget
add_action('widgets_init', function() {
    register_widget('Category_Accordion_Widget');
});

// ============================================
// Example 7: Gutenberg Block Integration
// ============================================

add_action('init', function() {
    if (!function_exists('register_block_type')) {
        return;
    }
    
    register_block_type('jankx-woo/category-accordion', [
        'attributes' => [
            'limit' => [
                'type' => 'number',
                'default' => 5,
            ],
            'style' => [
                'type' => 'string',
                'default' => 'card',
            ],
        ],
        'render_callback' => function($attributes) {
            $categories = get_terms([
                'taxonomy' => 'product_cat',
                'parent' => 0,
                'number' => $attributes['limit'],
                'hide_empty' => true,
            ]);
            
            $layoutManager = LayoutManager::getInstance();
            $layout = $layoutManager->get('expand-collapse-category');
            
            if (!$layout || empty($categories)) {
                return '';
            }
            
            // Set style
            $settingsManager = SettingsManager::getInstance();
            $settingsManager->set('expand-collapse-category.accordion_style', $attributes['style']);
            
            return $layout->render(['categories' => $categories]);
        },
    ]);
});

