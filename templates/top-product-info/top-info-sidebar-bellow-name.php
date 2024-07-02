<div id="jankx-ecommerce-top-prod-info">
    <?php jankx_open_container(); ?>

    <?php do_action('jankx/woocommerce/single/product/layout/top_info/before'); ?>

    <div class="info-with-sidebar-wrap">
        <div class="product-image-wrap">
            <?php do_action('jankx/woocommerce/single/product/layout/top_info/image'); ?>
        </div>

        <div class="main-product-info">
            <?php do_action('jankx/woocommerce/single/product/layout/top_info/main'); ?>

            <?php do_action('jankx/woocommerce/single/product/layout/top_info/before_sidebar'); ?>
            <div class="sidebar top-info-sidebar">
                <?php dynamic_sidebar('product_info_sidebar'); ?>
            </div>
            <?php do_action('jankx/woocommerce/single/product/layout/top_info/after_sidebar'); ?>
        </div>
    </div>

    <?php jankx_close_container(); ?>
</div>
