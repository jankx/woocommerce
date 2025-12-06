<?php
/**
 * Template: Modern Gallery Layout
 * 
 * @var WC_Product $product
 * @var string $main_image
 * @var string $thumbnails
 * @var string $video
 * @var string $view_360
 * @var string $gallery_type
 * @var string $thumbnail_position
 * @var AbstractProductGalleryLayout $layout
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="product-gallery product-gallery-<?php echo esc_attr($layout->getId()); ?> modern-gallery-wrapper">
    <?php echo $main_image; ?>
    
    <?php if (!empty($thumbnails)): ?>
        <?php echo $thumbnails; ?>
    <?php endif; ?>
    
    <?php if (!empty($video)): ?>
        <div class="product-gallery-video">
            <?php echo $video; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($view_360)): ?>
        <div class="product-gallery-360">
            <?php echo $view_360; ?>
        </div>
    <?php endif; ?>
</div>

<?php
// Render inline JavaScript
if (method_exists($layout, 'renderScript')) {
    $script = $layout->renderScript();
    if (!empty($script)) {
        // renderScript() already includes <script> tags
        echo $script;
    }
}
?>

