<?php
/**
 * Additional Information tab
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/tabs/additional-information.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.0.0
 */

defined('ABSPATH') || exit;

global $product;

ob_start();
do_action('woocommerce_product_additional_information', $product);
$product_additional_information = ob_get_clean();

if (empty($product_additional_information)) {
    return;
}

$heading = apply_filters('woocommerce_product_additional_information_heading', __('Additional information', 'woocommerce'));
?>

<?php if ($heading) : ?>
    <h2 class="product-attributes-heading"><?php echo esc_html($heading); ?></h2>
<?php endif; ?>

<?php echo $product_additional_information;
