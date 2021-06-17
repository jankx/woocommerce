<?php
/**
 * Recently Viewed Products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/recently_view.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 */

if (! defined('ABSPATH')) {
    exit;
}

if ($wp_query) : ?>
    <section class="recently-viewed products">

        <?php
        $heading = apply_filters(
            'woocommerce_product_similar_price_products_heading',
            __('Similar Price Products', 'woocommerce')
        );

        if ($heading) :
            ?>
            <h2><?php echo esc_html($heading); ?></h2>
        <?php endif; ?>

        <?php
            wc_set_loop_prop('name', 'viewed-products');
            wc_set_loop_prop('columns', apply_filters('jankx_ecommerce_viewed_products_columns', 4));
        ?>
        <?php woocommerce_product_loop_start(); ?>

            <?php while ($wp_query->have_posts()) : ?>
                <?php
                    $wp_query->the_post();
                    wc_get_template_part('content', 'product');
                ?>

            <?php endwhile; ?>

        <?php woocommerce_product_loop_end(); ?>

    </section>
    <?php
endif;

wp_reset_postdata();
