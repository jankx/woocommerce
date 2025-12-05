<?php

namespace Jankx\WooCommerce\Contracts\Layouts;

use Jankx\WooCommerce\Contracts\LayoutInterface;

/**
 * Interface CartPageLayoutInterface
 * 
 * Interface cho Cart Page layouts
 */
interface CartPageLayoutInterface extends LayoutInterface
{
    /**
     * Render cart page
     *
     * @param array $options Page options
     * @return string HTML
     */
    public function renderCartPage(array $options = []): string;

    /**
     * Render cart table
     *
     * @return string HTML
     */
    public function renderCartTable(): string;

    /**
     * Render cart actions (update cart, continue shopping)
     *
     * @return string HTML
     */
    public function renderCartActions(): string;

    /**
     * Render cross-sells section
     *
     * @return string HTML
     */
    public function renderCrossSells(): string;

    /**
     * Render cart collaterals (totals, shipping)
     *
     * @return string HTML
     */
    public function renderCartCollaterals(): string;

    /**
     * Render coupon form
     *
     * @return string HTML
     */
    public function renderCouponForm(): string;

    /**
     * Render shipping calculator
     *
     * @return string HTML
     */
    public function renderShippingCalculator(): string;

    /**
     * Render proceed to checkout button
     *
     * @return string HTML
     */
    public function renderCheckoutButton(): string;

    /**
     * Get page layout (sidebar, fullwidth)
     *
     * @return string
     */
    public function getPageLayout(): string;

    /**
     * Check if show cross-sells
     *
     * @return bool
     */
    public function showCrossSells(): bool;

    /**
     * Check if show shipping calculator
     *
     * @return bool
     */
    public function showShippingCalculator(): bool;
}

