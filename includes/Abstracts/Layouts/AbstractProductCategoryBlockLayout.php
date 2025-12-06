<?php

namespace Jankx\WooCommerce\Abstracts\Layouts;

use Jankx\WooCommerce\Abstracts\AbstractLayout;
use Jankx\WooCommerce\Contracts\Layouts\ProductCategoryBlockLayoutInterface;

/**
 * Abstract Class AbstractProductCategoryBlockLayout
 * 
 * Base implementation cho Product Category Block layouts
 */
abstract class AbstractProductCategoryBlockLayout extends AbstractLayout implements ProductCategoryBlockLayoutInterface
{
    /**
     * Layout type
     *
     * @var string
     */
    protected $type = 'product-category-block';

    /**
     * Display type
     *
     * @var string
     */
    protected $displayType = 'grid'; // grid, list, masonry

    /**
     * Show empty categories
     *
     * @var bool
     */
    protected $showEmptyCategories = false;

    /**
     * {@inheritDoc}
     */
    public function render(array $data = []): string
    {
        if (!isset($data['categories']) || !is_array($data['categories'])) {
            return '';
        }

        return $this->renderCategories($data['categories'], $data['options'] ?? []);
    }

    /**
     * {@inheritDoc}
     */
    public function renderCategories(array $categories, array $options = []): string
    {
        $template = $this->getTemplatePath();

        ob_start();
        echo '<div class="jankx-categories-block ' . esc_attr($this->getId()) . ' display-' . esc_attr($this->displayType) . '">';
        
        foreach ($categories as $category) {
            if ($category instanceof \WP_Term) {
                echo $this->renderCategory($category);
            }
        }
        
        echo '</div>';
        $output = ob_get_clean();
        
        // Add fingerprint for debugging
        return $this->wrapWithFingerprint($output);
    }

    /**
     * {@inheritDoc}
     */
    public function renderCategory(\WP_Term $category): string
    {
        $template = $this->getTemplatePath();

        return $this->loadTemplate($template, [
            'category' => $category,
            'layout' => $this,
            'thumbnail' => $this->renderCategoryThumbnail($category),
            'title' => $this->renderCategoryTitle($category),
            'count' => $this->renderProductCount($category),
            'description' => $this->renderCategoryDescription($category),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderCategoryThumbnail(\WP_Term $category): string
    {
        $thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
        
        if ($thumbnail_id) {
            return wp_get_attachment_image($thumbnail_id, 'woocommerce_thumbnail', false, [
                'class' => 'category-thumbnail',
                'alt' => $category->name
            ]);
        }

        return sprintf(
            '<img src="%s" alt="%s" class="category-thumbnail placeholder" />',
            wc_placeholder_img_src(),
            esc_attr($category->name)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function renderCategoryTitle(\WP_Term $category): string
    {
        return sprintf(
            '<h3 class="category-title"><a href="%s">%s</a></h3>',
            esc_url(get_term_link($category)),
            esc_html($category->name)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function renderProductCount(\WP_Term $category): string
    {
        return sprintf(
            '<span class="product-count">%d %s</span>',
            $category->count,
            _n('product', 'products', $category->count, 'jankx-woocommerce')
        );
    }

    /**
     * {@inheritDoc}
     */
    public function renderCategoryDescription(\WP_Term $category): string
    {
        if (empty($category->description)) {
            return '';
        }

        return '<div class="category-description">' . wp_kses_post($category->description) . '</div>';
    }

    /**
     * {@inheritDoc}
     */
    public function getDisplayType(): string
    {
        return $this->displayType;
    }

    /**
     * Set display type
     *
     * @param string $type
     * @return self
     */
    public function setDisplayType(string $type): self
    {
        $this->displayType = $type;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function showEmptyCategories(): bool
    {
        return $this->showEmptyCategories;
    }

    /**
     * {@inheritDoc}
     */
    protected function generateDynamicCss(array $settings): string
    {
        $css = '';

        if (isset($settings['columns'])) {
            $css .= sprintf(
                '.category-block-%s .category-item { width: %s%%; }',
                $this->id,
                100 / intval($settings['columns'])
            );
        }

        if (isset($settings['image_ratio'])) {
            $css .= sprintf(
                '.category-block-%s .category-thumbnail { aspect-ratio: %s; }',
                $this->id,
                esc_attr($settings['image_ratio'])
            );
        }

        return $css;
    }
}

