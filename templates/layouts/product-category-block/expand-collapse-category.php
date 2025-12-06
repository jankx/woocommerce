<?php
/**
 * Template for Expand/Collapse Category Layout - Single Category
 * 
 * @var \WP_Term $category
 * @var string $thumbnail
 * @var string $title
 * @var string $count
 * @var string $description
 */

defined('ABSPATH') || exit;

$has_children = false;
$children = get_term_children($category->term_id, 'product_cat');
if (!empty($children) && !is_wp_error($children)) {
    $has_children = true;
}
?>

<div class="category-accordion-item" data-category-id="<?php echo esc_attr($category->term_id); ?>">
    <div class="category-header" role="button" tabindex="0">
        <div class="category-main">
            <?php echo $thumbnail; ?>
            <div class="category-info">
                <?php echo $title; ?>
                <?php echo $count; ?>
                <?php if (!empty($description)): ?>
                    <?php echo $description; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($has_children): ?>
        <button class="category-toggle" aria-label="<?php esc_attr_e('Toggle category', 'jankx-woocommerce'); ?>">
            <span class="toggle-icon">
                <span class="icon-expand">+</span>
                <span class="icon-collapse">-</span>
            </span>
        </button>
        <?php endif; ?>
    </div>

    <?php if ($has_children): ?>
    <div class="category-content" style="display: none;">
        <!-- Subcategories and products preview will be rendered here -->
    </div>
    <?php endif; ?>
</div>

