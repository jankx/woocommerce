<?php

namespace Jankx\WooCommerce\PostLayout;

use Jankx\Layouts\PostLayout\Contracts\ContentGeneratorInterface;
use WP_Query;

/**
 * WooCommerce Content Generator
 *
 * Generator riêng cho WooCommerce products với đầy đủ chức năng WC
 *
 * @package Jankx\WooCommerce\PostLayout
 */
class WooCommerceContentGenerator implements ContentGeneratorInterface
{
    /**
     * Generator name
     *
     * @var string
     */
    protected $name = 'woocommerce';

    /**
     * Generator title
     *
     * @var string
     */
    protected $title = 'WooCommerce Products';

    /**
     * Supported options
     *
     * @var array
     */
    protected $supportedOptions = [
        'columns',
        'showFeaturedImage',
        'showTitle',
        'showPrice',
        'showRating',
        'showAddToCart',
        'showSaleBadge',
        'postsPerPage',
        'imageSize',
    ];

    /**
     * Check if WooCommerce is active
     *
     * @return bool
     */
    protected function isWooCommerceActive(): bool
    {
        return class_exists('WooCommerce');
    }

    /**
     * {@inheritDoc}
     */
    public function generate(WP_Query $query, array $options = []): string
    {
        if (!$this->isWooCommerceActive()) {
            return '<div class="woocommerce-error">' . __('WooCommerce is not active', 'jankx') . '</div>';
        }

        if (!$query->have_posts()) {
            return '<div class="no-products">' . __('No products found.', 'woocommerce') . '</div>';
        }

        // Save original globals
        global $wp_query, $product;
        $original_query = $wp_query;
        $original_product = isset($product) ? $product : null;

        // Set query to use WooCommerce template
        $wp_query = $query;

        $columns = $options['columns'] ?? 3;
        $showFeaturedImage = $options['showFeaturedImage'] ?? true;
        $showTitle = $options['showTitle'] ?? true;
        $showPrice = $options['showPrice'] ?? true;
        $showAddToCart = $options['showAddToCart'] ?? true;
        $showSaleBadge = $options['showSaleBadge'] ?? true;
        $imageSize = $options['imageSize'] ?? 'woocommerce_thumbnail';

        ob_start();
        ?>
        <ul data-block-name="woocommerce/product-template" class="wc-block-product-template__responsive columns-<?php echo esc_attr($columns); ?> wc-block-product-template wp-block-woocommerce-product-template is-layout-flow wp-block-product-template-is-layout-flow">
            <?php
            // Use WooCommerce product loop
            while ($query->have_posts()) {
                $query->the_post();
                global $product;
                
                // Setup product data for WooCommerce
                wc_setup_product_data($GLOBALS['post']);
                
                $product_id = $product->get_id();
                $product_type = $product->get_type();
                $product_classes = $this->getProductClasses($product);
                $cart_quantity = $this->getCartQuantity($product_id);
                $is_in_cart = $cart_quantity > 0;
                
                
                ?>
                <li class="<?php echo esc_attr($product_classes); ?>" data-wp-interactive="woocommerce/product-collection" data-wp-context="<?php echo esc_attr(json_encode(['productId' => $product_id])); ?>" data-wp-key="product-item-<?php echo esc_attr($product_id); ?>">
                    
                    <?php if ($showFeaturedImage): ?>
                    <div data-block-name="woocommerce/product-image" data-image-sizing="thumbnail" data-is-descendent-of-query-loop="true" data-show-sale-badge="<?php echo $showSaleBadge ? 'true' : 'false'; ?>" class="wc-block-components-product-image wc-block-grid__product-image wp-block-woocommerce-product-image">
                        <a href="<?php echo esc_url(get_permalink()); ?>" style="" data-wp-on--click="woocommerce/product-collection::actions.viewProduct">
                            <?php echo $product->get_image($imageSize, [
                                'class' => 'attachment-woocommerce_thumbnail size-woocommerce_thumbnail', 
                                'style' => 'object-fit:cover;', 
                                'data-testid' => 'product-image', 
                                'data-image-id' => get_post_thumbnail_id()
                            ]); ?>
                            <div class="wc-block-components-product-image__inner-container">
                                <?php if ($showSaleBadge && $product->is_on_sale()): ?>
                                <div data-block-name="woocommerce/product-sale-badge" class="wp-block-woocommerce-product-sale-badge">
                                    <div class="wc-block-components-product-sale-badge alignright wc-block-components-product-sale-badge--align-right">
                                        <span class="wc-block-components-product-sale-badge__text" aria-hidden="true"><?php echo esc_html__('Khuyến mại', 'woocommerce'); ?></span>
                                        <span class="screen-reader-text"><?php echo esc_html__('Sản phẩm đang giảm giá', 'woocommerce'); ?></span>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </a>
                    </div>
                    <?php endif; ?>

                    <?php if ($showTitle): ?>
                    <h2 style="line-height:1.4; margin-bottom:0.75rem;margin-top:0;" class="has-text-align-center wp-block-post-title has-medium-font-size">
                        <a data-wp-on--click="woocommerce/product-collection::actions.viewProduct" href="<?php echo esc_url(get_permalink()); ?>" target="_self"><?php echo esc_html(get_the_title()); ?></a>
                    </h2>
                    <?php endif; ?>

                    <?php if ($showPrice): ?>
                    <div data-block-name="woocommerce/product-price" data-font-size="small" data-is-descendent-of-query-loop="true" data-text-align="center" class="wp-block-woocommerce-product-price">
                        <div class="wc-block-components-product-price wc-block-grid__product-price has-text-align-center has-font-size has-small-font-size has-text-align-center">
                            <?php echo $product->get_price_html(); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($showAddToCart): ?>
                    <div data-block-name="woocommerce/product-button" data-font-size="small" data-is-descendent-of-query-loop="true" data-text-align="center" class="wp-block-button wc-block-components-product-button align-center wp-block-woocommerce-product-button has-small-font-size" data-wp-interactive="woocommerce/product-button" data-wp-init="actions.refreshCartItems" data-wp-context="<?php echo esc_attr(json_encode([
                        'quantityToAdd' => 1,
                        'productId' => $product_id,
                        'productType' => $product_type,
                        'addToCartText' => __('Thêm vào giỏ hàng', 'woocommerce'),
                        'tempQuantity' => $cart_quantity,
                        'animationStatus' => 'IDLE',
                        'inTheCartText' => sprintf(__('Có ### trong giỏ hàng', 'woocommerce')),
                        'noticeId' => '',
                        'hasPressedButton' => false
                    ])); ?>">
                        
                        <?php if ($product->is_purchasable() && $product->is_in_stock()): ?>
                        <button class="wp-block-button__link wp-element-button wc-block-components-product-button__button add_to_cart_button ajax_add_to_cart product_type_<?php echo esc_attr($product_type); ?> has-font-size has-small-font-size has-text-align-center wc-interactive" style="" type="button" data-product_id="<?php echo esc_attr($product_id); ?>" data-product_sku="<?php echo esc_attr($product->get_sku()); ?>" aria-label="<?php echo esc_attr(sprintf(__('Thêm vào giỏ hàng: "%s"', 'woocommerce'), get_the_title())); ?>" data-wp-on--click="actions.addCartItem">
                            <span data-wp-text="state.addToCartText" data-wp-class--wc-block-slide-in="state.slideInAnimation" data-wp-class--wc-block-slide-out="state.slideOutAnimation" data-wp-on--animationend="actions.handleAnimationEnd" data-wp-watch="callbacks.startAnimation" data-wp-run="callbacks.syncTempQuantityOnLoad" data-wp-on--click="actions.handlePressedState" class="">
                                <?php echo $is_in_cart ? sprintf(__('Có %d trong giỏ hàng', 'woocommerce'), $cart_quantity) : __('Thêm vào giỏ hàng', 'woocommerce'); ?>
                            </span>
                        </button>
                        <?php else: ?>
                        <a class="wp-block-button__link wp-element-button wc-block-components-product-button__button product_type_<?php echo esc_attr($product_type); ?> has-font-size has-small-font-size has-text-align-center wc-interactive" style="" href="<?php echo esc_url(get_permalink()); ?>" rel="nofollow" data-product_id="<?php echo esc_attr($product_id); ?>" data-product_sku="<?php echo esc_attr($product->get_sku()); ?>" aria-label="<?php echo esc_attr(sprintf(__('Đọc thêm về "%s"', 'woocommerce'), get_the_title())); ?>" data-wp-on--click="woocommerce/product-collection::actions.viewProduct">
                            <span><?php echo esc_html__('Đọc tiếp', 'woocommerce'); ?></span>
                        </a>
                        <?php endif; ?>

                        <span hidden="" data-wp-bind--hidden="!state.displayViewCart">
                            <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="added_to_cart wc_forward" title="<?php echo esc_attr__('Xem giỏ hàng', 'woocommerce'); ?>">
                                <?php echo esc_html__('Xem giỏ hàng', 'woocommerce'); ?>
                            </a>
                        </span>
                    </div>
                    <?php endif; ?>

                </li>
                <?php
                
            }
            ?>
        </ul>
        <?php
        
        
        // Restore original globals
        wp_reset_postdata();
        $wp_query = $original_query;
        $product = $original_product;
        
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function generatePreview(array $options = []): array
    {
        return [
            'name' => $this->name,
            'title' => $this->title,
            'type' => 'woocommerce',
            'columns' => $options['columns'] ?? 3,
            'supportedOptions' => $this->supportedOptions,
            'previewItems' => $this->generatePreviewItems($options),
            'woocommerce' => true,
        ];
    }

    /**
     * Generate preview items
     *
     * @param array $options
     * @return array
     */
    protected function generatePreviewItems(array $options = []): array
    {
        $count = min($options['postsPerPage'] ?? 6, 6);
        $items = [];

        for ($i = 0; $i < $count; $i++) {
            $items[] = [
                'id' => $i + 1,
                'title' => sprintf(__('Product %d', 'jankx'), $i + 1),
                'price' => '$' . (29 + $i * 10) . '.99',
                'rating' => 4.0 + ($i % 2) * 0.5,
                'on_sale' => ($i % 3 === 0),
                'thumbnail' => true,
            ];
        }

        return $items;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsOptions(array $options): bool
    {
        if (empty($this->supportedOptions)) {
            return true;
        }

        foreach ($options as $key => $value) {
            if ($value !== false && !in_array($key, $this->supportedOptions, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get product classes for WooCommerce blocks compatibility
     *
     * @param \WC_Product $product
     * @return string
     */
    protected function getProductClasses($product): string
    {
        $classes = [
            'wc-block-product',
            'post-' . $product->get_id(),
            'product',
            'type-product',
            'status-' . $product->get_status(),
        ];

        // Add thumbnail class if product has featured image
        if ($product->get_image_id()) {
            $classes[] = 'has-post-thumbnail';
        }

        // Add product categories
        $categories = wp_get_post_terms($product->get_id(), 'product_cat', ['fields' => 'slugs']);
        if (!empty($categories)) {
            $classes = array_merge($classes, array_map(function($cat) {
                return 'product_cat-' . $cat;
            }, $categories));
        }

        // Add stock status
        if ($product->is_in_stock()) {
            $classes[] = 'instock';
        } else {
            $classes[] = 'outofstock';
        }

        // Add shipping taxable
        if ($product->is_shipping_taxable()) {
            $classes[] = 'shipping-taxable';
        }

        // Add purchasable
        if ($product->is_purchasable()) {
            $classes[] = 'purchasable';
        }

        // Add sale status
        if ($product->is_on_sale()) {
            $classes[] = 'sale';
        }

        // Add product type
        $classes[] = 'product-type-' . $product->get_type();

        return implode(' ', $classes);
    }

    /**
     * Get cart quantity for product
     *
     * @param int $product_id
     * @return int
     */
    protected function getCartQuantity(int $product_id): int
    {
        if (!WC()->cart) {
            return 0;
        }

        $cart_items = WC()->cart->get_cart();
        foreach ($cart_items as $cart_item) {
            if ($cart_item['product_id'] == $product_id) {
                return $cart_item['quantity'];
            }
        }

        return 0;
    }
}

