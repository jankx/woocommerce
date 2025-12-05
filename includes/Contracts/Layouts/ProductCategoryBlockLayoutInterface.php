<?php

namespace Jankx\WooCommerce\Contracts\Layouts;

use Jankx\WooCommerce\Contracts\LayoutInterface;

/**
 * Interface ProductCategoryBlockLayoutInterface
 * 
 * Interface cho Product Category Block List layouts
 */
interface ProductCategoryBlockLayoutInterface extends LayoutInterface
{
    /**
     * Render danh sách categories
     *
     * @param array $categories Array of WP_Term objects
     * @param array $options Display options
     * @return string HTML
     */
    public function renderCategories(array $categories, array $options = []): string;

    /**
     * Render single category item
     *
     * @param \WP_Term $category
     * @return string HTML
     */
    public function renderCategory(\WP_Term $category): string;

    /**
     * Render category thumbnail
     *
     * @param \WP_Term $category
     * @return string HTML
     */
    public function renderCategoryThumbnail(\WP_Term $category): string;

    /**
     * Render category title
     *
     * @param \WP_Term $category
     * @return string HTML
     */
    public function renderCategoryTitle(\WP_Term $category): string;

    /**
     * Render product count
     *
     * @param \WP_Term $category
     * @return string HTML
     */
    public function renderProductCount(\WP_Term $category): string;

    /**
     * Render category description
     *
     * @param \WP_Term $category
     * @return string HTML
     */
    public function renderCategoryDescription(\WP_Term $category): string;

    /**
     * Get display type (grid, list, masonry)
     *
     * @return string
     */
    public function getDisplayType(): string;

    /**
     * Check if show empty categories
     *
     * @return bool
     */
    public function showEmptyCategories(): bool;
}

