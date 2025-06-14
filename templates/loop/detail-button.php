<?php 
if (!defined('ABSPATH')) {
    exit('Cheatin huh?');
}
 ?>
<?php woocommerce_template_loop_product_link_open(); ?>
    <span><?php _e('Product Details', 'woocommerce'); ?></span>
<?php woocommerce_template_loop_product_link_close();
