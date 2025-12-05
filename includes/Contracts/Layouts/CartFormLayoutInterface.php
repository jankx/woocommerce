<?php

namespace Jankx\WooCommerce\Contracts\Layouts;

use Jankx\WooCommerce\Contracts\LayoutInterface;

/**
 * Interface CartFormLayoutInterface
 * 
 * Interface cho Cart Form layouts (mini cart, cart widget)
 */
interface CartFormLayoutInterface extends LayoutInterface
{
    /**
     * Render cart form
     *
     * @param array $cartItems Cart items
     * @param array $options Display options
     * @return string HTML
     */
    public function renderCartForm(array $cartItems, array $options = []): string;

    /**
     * Render single cart item
     *
     * @param array $cartItem Cart item data
     * @return string HTML
     */
    public function renderCartItem(array $cartItem): string;

    /**
     * Render item thumbnail
     *
     * @param array $cartItem
     * @return string HTML
     */
    public function renderItemThumbnail(array $cartItem): string;

    /**
     * Render item details
     *
     * @param array $cartItem
     * @return string HTML
     */
    public function renderItemDetails(array $cartItem): string;

    /**
     * Render quantity selector
     *
     * @param array $cartItem
     * @return string HTML
     */
    public function renderQuantitySelector(array $cartItem): string;

    /**
     * Render item price
     *
     * @param array $cartItem
     * @return string HTML
     */
    public function renderItemPrice(array $cartItem): string;

    /**
     * Render remove button
     *
     * @param array $cartItem
     * @return string HTML
     */
    public function renderRemoveButton(array $cartItem): string;

    /**
     * Render cart totals summary
     *
     * @return string HTML
     */
    public function renderCartTotals(): string;

    /**
     * Check if show thumbnails
     *
     * @return bool
     */
    public function showThumbnails(): bool;

    /**
     * Check if editable (quantity change, remove)
     *
     * @return bool
     */
    public function isEditable(): bool;
}

