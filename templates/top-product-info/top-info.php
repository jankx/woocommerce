<div id="jankx-ecommerce-top-prod-info">
    <?php jankx_open_container(); ?>

    <?php do_action('jankx/ecommerce/single/product/layout/top_info/before'); ?>

    <?php if ($has_sidebar): ?>
        <div class="info-with-sidebar-wrap">
            <div class="product-top-info">
                <div class="product-image-wrap">
                    <?php do_action('jankx/ecommerce/single/product/layout/top_info/image'); ?>
                </div>

                <div class="main-product-info">
                    <?php do_action('jankx/ecommerce/single/product/layout/top_info/main'); ?>
                </div>
            </div>
            <div class="sidebar top-info-sidebar">
                <?php dynamic_sidebar('product_info_sidebar'); ?>
            </div>
        </div>
    <?php else: ?>
        <div class="product-top-info">
            <div class="product-image-wrap">
                <?php do_action('jankx/ecommerce/single/product/layout/top_info/image'); ?>
            </div>

            <div class="main-product-info">
                <?php do_action('jankx/ecommerce/single/product/layout/top_info/main'); ?>
            </div>
        </div>
    <?php endif; ?>
    <?php jankx_close_container(); ?>
</div>
