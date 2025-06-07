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
use Jankx\WooCommerce\Renderer\ProductsRenderer;
use Jankx\WooCommerce\WooCommerceTemplate;

if (! defined('ABSPATH')) {
    exit;
}
if (!empty($related_products)) : ?>
    <?php

        $args = apply_filters('jankx/woocommerce/product/related/layout_args', wp_parse_args($args, array(
            'layout' => Card::LAYOUT_NAME,
            'columns' => 4,
        )));

        // Get ecommerce template Engine
        $engine = WooCommerceTemplate::getEngine();
        $postLayoutManager = PostLayoutManager::getInstance($engine);
        $postLayout = $postLayoutManager->createLayout(
            array_get($args, 'layout', Card::LAYOUT_NAME),
            $related_products
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

        <?php
        $settings = [];
        $productsModule = new ProductsRenderer(array(
            'layout' => array_get($args, 'layout'),
        ));
        $productsModule->setMainQuery($related_products);

        if (($url = array_get($settings, 'readmore_url', ''))) {
            $productsModule->setReadMore($url);
        }

        $productsModule->setLayoutOptions(array(
            'columns_tablet' => 2,
            'columns_mobile' => 1,
            'columns' => 4,
            'rows' => 1,
            'thumbnail_size'  => 'medium',
        ));
        // Set Woocommerce loop columns
        wc_get_loop_prop('columns', array_get($args, 'columns'));

        // Render the content
        echo $productsContent = $productsModule->render();
        ?>
    </section>
    <?php
endif;
