<?php
/**
 * Template for Grid Product Loop Layout - Single Item
 * 
 * @var WC_Product $product
 * @var string $thumbnail
 * @var string $title
 * @var string $price
 * @var string $badges
 * @var string $add_to_cart
 * @var string $quick_view
 * @var string $columns_class
 */

defined('ABSPATH') || exit;
?>

<div class="product-item <?php echo esc_attr($columns_class); ?>">
    <div class="product-thumbnail">
        <a href="<?php echo esc_url($product->get_permalink()); ?>">
            <?php echo $thumbnail; ?>
        </a>
        <?php echo $badges; ?>
    </div>
    
    <div class="product-content">
        <?php echo $title; ?>
        <?php echo $price; ?>
        <?php echo $add_to_cart; ?>
    </div>
    
    <?php if (!empty($quick_view)): ?>
        <?php echo $quick_view; ?>
    <?php endif; ?>
</div>

