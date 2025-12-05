<?php

namespace Jankx\WooCommerce\Contracts\Layouts;

use Jankx\WooCommerce\Contracts\LayoutInterface;

/**
 * Interface CheckoutPageLayoutInterface
 * 
 * Interface cho Checkout Page layouts
 */
interface CheckoutPageLayoutInterface extends LayoutInterface
{
    /**
     * Render checkout page
     *
     * @param array $options Page options
     * @return string HTML
     */
    public function renderCheckoutPage(array $options = []): string;

    /**
     * Render billing fields
     *
     * @return string HTML
     */
    public function renderBillingFields(): string;

    /**
     * Render shipping fields
     *
     * @return string HTML
     */
    public function renderShippingFields(): string;

    /**
     * Render order review section
     *
     * @return string HTML
     */
    public function renderOrderReview(): string;

    /**
     * Render payment methods
     *
     * @return string HTML
     */
    public function renderPaymentMethods(): string;

    /**
     * Render order notes field
     *
     * @return string HTML
     */
    public function renderOrderNotes(): string;

    /**
     * Render terms and conditions
     *
     * @return string HTML
     */
    public function renderTermsAndConditions(): string;

    /**
     * Render place order button
     *
     * @return string HTML
     */
    public function renderPlaceOrderButton(): string;

    /**
     * Render login form (nếu guest checkout)
     *
     * @return string HTML
     */
    public function renderLoginForm(): string;

    /**
     * Render coupon form
     *
     * @return string HTML
     */
    public function renderCouponForm(): string;

    /**
     * Get checkout layout (single-column, two-column, multi-step)
     *
     * @return string
     */
    public function getCheckoutLayout(): string;

    /**
     * Check if multi-step checkout
     *
     * @return bool
     */
    public function isMultiStep(): bool;

    /**
     * Get number of steps (nếu multi-step)
     *
     * @return int
     */
    public function getStepsCount(): int;
}

