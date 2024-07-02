<?php
/**
 * Related Products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/related.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     3.9.0
 */

use Jankx\PostLayout\PostLayoutManager;
use Jankx\PostLayout\Layout\Card;
use Jankx\Ecommerce\EcommerceTemplate;

if (! defined('ABSPATH')) {
    exit;
}

if (!empty($related_products)) : ?>
    <?php
        $wp_query = new WP_Query();
        $wp_query->set('post_type', 'product');
        $wp_query->posts = $related_products;
        $wp_query->post_count = count($related_products);

        $args = apply_filters('jankx/woocommerce/product/related/layout_args', wp_parse_args($args, array(
            'layout' => Card::LAYOUT_NAME,
            'columns' => 4,
        )));

        // Get ecommerce template Engine
        $engine = EcommerceTemplate::getEngine();
        $postLayoutManager = PostLayoutManager::getInstance($engine);
        $postLayout = $postLayoutManager->createLayout(
            array_get($args, 'layout', Card::LAYOUT_NAME),
            $wp_query
        );
        $postLayout->setOptions($args);
    ?>
    <section class="related products">

        <?php
        $heading = apply_filters('woocommerce_product_related_products_heading', __('Related products', 'woocommerce'));

        if ($heading) :
            ?>
            <h2><?php echo esc_html($heading); ?></h2>
        <?php endif; ?>

        <?php $postLayout->render(); ?>
    </section>
    <?php
endif;
