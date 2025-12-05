<?php

namespace Jankx\WooCommerce\Abstracts\Layouts;

use Jankx\WooCommerce\Abstracts\AbstractLayout;
use Jankx\WooCommerce\Contracts\Layouts\CheckoutPageLayoutInterface;

/**
 * Abstract Class AbstractCheckoutPageLayout
 * 
 * Base implementation cho Checkout Page layouts
 */
abstract class AbstractCheckoutPageLayout extends AbstractLayout implements CheckoutPageLayoutInterface
{
    /**
     * Layout type
     *
     * @var string
     */
    protected $type = 'checkout-page';

    /**
     * Checkout layout
     *
     * @var string
     */
    protected $checkoutLayout = 'two-column'; // single-column, two-column, multi-step

    /**
     * Multi-step checkout
     *
     * @var bool
     */
    protected $isMultiStep = false;

    /**
     * Steps count
     *
     * @var int
     */
    protected $stepsCount = 3;

    /**
     * {@inheritDoc}
     */
    public function render(array $data = []): string
    {
        return $this->renderCheckoutPage($data['options'] ?? []);
    }

    /**
     * {@inheritDoc}
     */
    public function renderCheckoutPage(array $options = []): string
    {
        $template = $this->getTemplatePath();

        return $this->loadTemplate($template, [
            'layout' => $this,
            'billing_fields' => $this->renderBillingFields(),
            'shipping_fields' => $this->renderShippingFields(),
            'order_review' => $this->renderOrderReview(),
            'payment_methods' => $this->renderPaymentMethods(),
            'order_notes' => $this->renderOrderNotes(),
            'terms' => $this->renderTermsAndConditions(),
            'place_order' => $this->renderPlaceOrderButton(),
            'coupon_form' => $this->renderCouponForm(),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderBillingFields(): string
    {
        ob_start();
        if (WC()->checkout()) {
            $checkout = WC()->checkout();
            $fields = $checkout->get_checkout_fields('billing');
            
            echo '<div class="billing-fields">';
            echo '<h3>' . __('Billing details', 'jankx-woocommerce') . '</h3>';
            
            foreach ($fields as $key => $field) {
                woocommerce_form_field($key, $field, $checkout->get_value($key));
            }
            
            echo '</div>';
        }
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function renderShippingFields(): string
    {
        ob_start();
        if (WC()->checkout()) {
            $checkout = WC()->checkout();
            $fields = $checkout->get_checkout_fields('shipping');
            
            echo '<div class="shipping-fields">';
            echo '<h3>' . __('Shipping details', 'jankx-woocommerce') . '</h3>';
            
            foreach ($fields as $key => $field) {
                woocommerce_form_field($key, $field, $checkout->get_value($key));
            }
            
            echo '</div>';
        }
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function renderOrderReview(): string
    {
        ob_start();
        woocommerce_order_review();
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function renderPaymentMethods(): string
    {
        ob_start();
        woocommerce_checkout_payment();
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function renderOrderNotes(): string
    {
        ob_start();
        if (WC()->checkout()) {
            $checkout = WC()->checkout();
            $fields = $checkout->get_checkout_fields('order');
            
            foreach ($fields as $key => $field) {
                woocommerce_form_field($key, $field, $checkout->get_value($key));
            }
        }
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function renderTermsAndConditions(): string
    {
        ob_start();
        woocommerce_checkout_terms_and_conditions();
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function renderPlaceOrderButton(): string
    {
        ob_start();
        woocommerce_order_button_html();
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function renderLoginForm(): string
    {
        ob_start();
        woocommerce_checkout_login_form();
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function renderCouponForm(): string
    {
        ob_start();
        woocommerce_checkout_coupon_form();
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function getCheckoutLayout(): string
    {
        return $this->checkoutLayout;
    }

    /**
     * {@inheritDoc}
     */
    public function isMultiStep(): bool
    {
        return $this->isMultiStep;
    }

    /**
     * {@inheritDoc}
     */
    public function getStepsCount(): int
    {
        return $this->stepsCount;
    }

    /**
     * {@inheritDoc}
     */
    protected function generateDynamicCss(array $settings): string
    {
        $css = '';

        if (isset($settings['field_spacing'])) {
            $css .= sprintf(
                '.checkout-page-%s .form-row { margin-bottom: %dpx; }',
                $this->id,
                intval($settings['field_spacing'])
            );
        }

        if (isset($settings['button_color'])) {
            $css .= sprintf(
                '.checkout-page-%s #place_order { background-color: %s; }',
                $this->id,
                esc_attr($settings['button_color'])
            );
        }

        return $css;
    }
}

