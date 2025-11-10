<?php

namespace Jankx\WooCommerce\SmartTabs;

use Jankx\Gutenberg\SmartTabs\AbstractSmartTabTrigger;

class ProductReviewsTrigger extends AbstractSmartTabTrigger
{
    /**
     * {@inheritdoc}
     */
    public function getKey(): string
    {
        return 'product-reviews';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return __('Product Reviews', 'woocommerce');
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return __('Display customer reviews for the current product and show the review count in the tab title.', 'woocommerce');
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailable(array $context = []): bool
    {
        return class_exists('WooCommerce');
    }

    /**
     * {@inheritdoc}
     */
    public function getEditorSettings(array $context = []): array
    {
        $settings = parent::getEditorSettings($context);
        $settings['supports'] = [
            'customTitle' => false,
            'customContent' => false,
            'icon' => true,
        ];

        return $settings;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveTitle(array $attributes, array $context = []): string
    {
        if (!function_exists('wc_get_product')) {
            return __('Reviews', 'woocommerce');
        }

        $post_id = isset($context['post_id']) ? (int) $context['post_id'] : 0;

        if ($post_id <= 0) {
            return __('Reviews', 'woocommerce');
        }

        $product = wc_get_product($post_id);
        if (!$product) {
            return __('Reviews', 'woocommerce');
        }

        $review_count = (int) $product->get_review_count();

        if ($review_count > 0) {
            return sprintf(
                /* translators: %d: number of product reviews */
                __('Reviews (%d)', 'woocommerce'),
                $review_count
            );
        }

        return __('Reviews', 'woocommerce');
    }

    /**
     * {@inheritdoc}
     */
    public function filterContent(string $content, array $attributes, array $context = []): string
    {
        if (!function_exists('wc_get_product')) {
            return $content;
        }

        $post_id = isset($context['post_id']) ? (int) $context['post_id'] : 0;

        if ($post_id <= 0) {
            return $content;
        }

        $wc_product = wc_get_product($post_id);
        if (!$wc_product) {
            return $content;
        }

        global $product, $post;

        $previous_product = $product;
        $previous_post = $post;

        $product = wc_get_product($post_id);
        $post = get_post($post_id);

        ob_start();
        comments_template('/single-product-reviews.php');
        $reviews_html = ob_get_clean();

        $product = $previous_product;
        $post = $previous_post;

        if (!empty($reviews_html)) {
            return $reviews_html;
        }

        return $content;
    }
}


