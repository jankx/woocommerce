<?php
global $post;

setup_postdata($post);

do_action('woocommerce_shop_loop');
wc_get_template_part('content', 'product');
wp_reset_postdata();
