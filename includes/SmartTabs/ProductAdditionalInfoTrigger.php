<?php

namespace Jankx\WooCommerce\SmartTabs;

use Jankx\Gutenberg\SmartTabs\AbstractSmartTabTrigger;

class ProductAdditionalInfoTrigger extends AbstractSmartTabTrigger
{
    /**
     * {@inheritdoc}
     */
    public function getKey(): string
    {
        return 'product-additional-info';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return __('Additional Information', 'woocommerce');
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return __('Display product attributes (additional information). Tab is hidden automatically when the product has no attributes.', 'woocommerce');
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
            'customTitle' => true,
            'customContent' => false,
            'icon' => true,
        ];
        $settings['previewTitle'] = __('Additional Information', 'woocommerce');

        return $settings;
    }

    /**
     * Determine if tab should be displayed.
     *
     * @param array $attributes
     * @param array $context
     * @return bool
     */
    public function shouldDisplay(array $attributes, array $context = []): bool
    {
        if (!function_exists('wc_get_product')) {
            return false;
        }

        $post_id = isset($context['post_id']) ? (int) $context['post_id'] : 0;

        if ($post_id <= 0) {
            return false;
        }

        $wc_product = wc_get_product($post_id);
        if (!$wc_product) {
            return false;
        }

        $attributes = $wc_product->get_attributes();
        if (empty($attributes)) {
            return false;
        }

        foreach ($attributes as $attribute) {
            if ($attribute->is_taxonomy()) {
                $options = wc_get_product_terms($wc_product->get_id(), $attribute->get_taxonomy(), ['fields' => 'all']);
                if (!empty($options)) {
                    return true;
                }
            } else {
                $values = $attribute->get_options();
                if (!empty(array_filter($values))) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveTitle(string $baseTitle, array $attributes, array $context = []): string
    {
        if ($baseTitle !== '') {
            return $baseTitle;
        }

        return __('Additional Information', 'woocommerce');
    }

    /**
     * {@inheritdoc}
     */
    public function filterContent(string $content, array $attributes, array $context = []): string
    {
        if (!function_exists('wc_get_template')) {
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

        global $product;
        $previous_product = $product;
        $product = $wc_product;

        ob_start();
        wc_get_template('single-product/tabs/additional-information.php', [
            'product' => $wc_product,
        ]);
        $additional_html = ob_get_clean();

        $product = $previous_product;

        if (!empty($additional_html)) {
            return $additional_html;
        }

        return $content;
    }
}


