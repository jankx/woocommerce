<?php

namespace Jankx\WooCommerce\Contracts\Layouts;

use Jankx\WooCommerce\Contracts\LayoutInterface;

/**
 * Interface QuickCheckoutLayoutInterface
 * 
 * Interface cho Quick Checkout layouts (custom feature)
 */
interface QuickCheckoutLayoutInterface extends LayoutInterface
{
    /**
     * Render quick checkout modal/popup
     *
     * @param array $options Modal options
     * @return string HTML
     */
    public function renderQuickCheckout(array $options = []): string;

    /**
     * Render simplified billing form
     *
     * @return string HTML
     */
    public function renderSimplifiedBillingForm(): string;

    /**
     * Render product summary trong quick checkout
     *
     * @return string HTML
     */
    public function renderProductSummary(): string;

    /**
     * Render quick payment methods (ví dụ: chỉ COD và popular methods)
     *
     * @return string HTML
     */
    public function renderQuickPaymentMethods(): string;

    /**
     * Render instant place order button
     *
     * @return string HTML
     */
    public function renderInstantOrderButton(): string;

    /**
     * Render progress indicator
     *
     * @return string HTML
     */
    public function renderProgressIndicator(): string;

    /**
     * Get trigger type (button, modal, slide-in)
     *
     * @return string
     */
    public function getTriggerType(): string;

    /**
     * Check if supports one-click checkout
     *
     * @return bool
     */
    public function supportsOneClick(): bool;

    /**
     * Check if can use saved addresses
     *
     * @return bool
     */
    public function supportsSavedAddresses(): bool;

    /**
     * Get required fields only
     *
     * @return array
     */
    public function getRequiredFields(): array;
}

