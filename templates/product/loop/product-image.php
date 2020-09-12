<?php do_action('jankx_ecommerce_before_product_image', $product); ?>
    <div class="jankx-ui-image product-image">
        <?php jankx_the_post_thumbnail('medium'); ?>
    </div>
<?php do_action('jankx_ecommerce_after_product_image', $product, ); ?>
