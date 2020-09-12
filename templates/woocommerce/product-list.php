<?php
wc_set_loop_prop( 'name', 'related' );
wc_set_loop_prop( 'columns', apply_filters( 'jankx_tabs_products_columns', $columns ) );

if ($wp_query->have_posts()) {
    woocommerce_product_loop_start();

    while ($wp_query->have_posts()) {
        $wp_query->the_post();
        wc_get_template_part('content', 'product');
    }

    woocommerce_product_loop_end();
}
