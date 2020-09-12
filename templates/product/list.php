<?php
if (!is_a($wp_query, WP_Query::class)) {
    return;
}
?>
<div class="jankx-ecommerce-products">
    <?php if ($wp_query->have_posts()) {
        while ($wp_query->have_posts()) {
            $wp_query->the_post();

            $t::render(
                'product/product-content',
                array('product' => $wp_query->post)
            );
        }
        wp_reset_postdata();
    }
    ?>
</div>
