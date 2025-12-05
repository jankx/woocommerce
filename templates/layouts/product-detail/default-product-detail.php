<?php
/**
 * Template for Default Product Detail Layout
 * 
 * @var WC_Product $product
 * @var string $gallery
 * @var string $summary
 * @var string $meta
 * @var string $tabs
 * @var string $related
 */

defined('ABSPATH') || exit;
?>

<div class="jankx-product-detail default-product-detail">
    <div class="product-main-content">
        <div class="product-gallery-wrapper">
            <?php echo $gallery; ?>
        </div>
        
        <div class="product-summary-wrapper">
            <?php echo $summary; ?>
            <?php echo $meta; ?>
        </div>
    </div>
    
    <div class="product-tabs-section">
        <?php echo $tabs; ?>
    </div>
    
    <?php if (!empty($related)): ?>
    <div class="product-related-section">
        <?php echo $related; ?>
    </div>
    <?php endif; ?>
</div>

