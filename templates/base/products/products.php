<div class="jankx-ecommerce products-widget">
    <h3 class="widget-title"><?php echo $widget_title; ?></h3>
    <div class="widget-content">
        <?php echo $products; ?>
    </div>
    <?php if (!empty($readmore)) : ?>
    <div class="jankx-read view-all-link">
        <a href="<?php echo array_get($readmore, 'url', '#');  ?>">
            <?php echo array_get($readmore, 'text', __('Read more', 'jankx')); ?>
        </a>
    </div>
    <?php endif; ?>
</div>
