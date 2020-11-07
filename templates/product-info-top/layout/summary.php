<div class="top-product-info">
    <?php jankx_open_container(); ?>
        <div class="top-product-info-content">
            <div class="product-info-and-actions">
                <div class="left-block">
                    <?php do_action('jankx_ecommerce_product_info_top_left_block'); ?>
                </div>
                <div class="right-block">
                    <?php do_action('jankx_ecommerce_product_info_top_right_block'); ?>
                </div>
            </div>
            <div class="product-info-sidebar">
                <?php dynamic_sidebar('product_info_sidebar'); ?>
            </div>
        </div>
    <?php jankx_close_container(); ?>
</div>
