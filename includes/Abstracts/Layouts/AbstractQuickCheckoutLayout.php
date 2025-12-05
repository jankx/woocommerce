<?php

namespace Jankx\WooCommerce\Abstracts\Layouts;

use Jankx\WooCommerce\Abstracts\AbstractLayout;
use Jankx\WooCommerce\Contracts\Layouts\QuickCheckoutLayoutInterface;

/**
 * Abstract Class AbstractQuickCheckoutLayout
 * 
 * Base implementation cho Quick Checkout layouts
 */
abstract class AbstractQuickCheckoutLayout extends AbstractLayout implements QuickCheckoutLayoutInterface
{
    /**
     * Layout type
     *
     * @var string
     */
    protected $type = 'quick-checkout';

    /**
     * Trigger type
     *
     * @var string
     */
    protected $triggerType = 'modal'; // modal, slide-in, inline

    /**
     * One-click support
     *
     * @var bool
     */
    protected $supportsOneClick = false;

    /**
     * Saved addresses support
     *
     * @var bool
     */
    protected $supportsSavedAddresses = true;

    /**
     * Required fields
     *
     * @var array
     */
    protected $requiredFields = ['billing_phone', 'billing_email', 'billing_address_1'];

    /**
     * {@inheritDoc}
     */
    public function render(array $data = []): string
    {
        return $this->renderQuickCheckout($data['options'] ?? []);
    }

    /**
     * {@inheritDoc}
     */
    public function renderQuickCheckout(array $options = []): string
    {
        $template = $this->getTemplatePath();

        return $this->loadTemplate($template, [
            'layout' => $this,
            'billing_form' => $this->renderSimplifiedBillingForm(),
            'product_summary' => $this->renderProductSummary(),
            'payment_methods' => $this->renderQuickPaymentMethods(),
            'order_button' => $this->renderInstantOrderButton(),
            'progress' => $this->renderProgressIndicator(),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderSimplifiedBillingForm(): string
    {
        ob_start();
        echo '<div class="quick-billing-form">';
        
        foreach ($this->requiredFields as $fieldKey) {
            if (WC()->checkout()) {
                $checkout = WC()->checkout();
                $fields = $checkout->get_checkout_fields('billing');
                
                if (isset($fields[$fieldKey])) {
                    woocommerce_form_field($fieldKey, $fields[$fieldKey], $checkout->get_value($fieldKey));
                }
            }
        }
        
        echo '</div>';
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function renderProductSummary(): string
    {
        ob_start();
        echo '<div class="quick-product-summary">';
        woocommerce_order_review();
        echo '</div>';
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function renderQuickPaymentMethods(): string
    {
        ob_start();
        echo '<div class="quick-payment-methods">';
        
        // Chỉ hiển thị popular payment methods
        $available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
        $popular_methods = ['cod', 'bacs', 'momo', 'vnpay']; // Customize based on region
        
        foreach ($available_gateways as $gateway) {
            if (in_array($gateway->id, $popular_methods)) {
                echo '<div class="payment-method">';
                echo '<input type="radio" name="payment_method" value="' . esc_attr($gateway->id) . '" />';
                echo '<label>' . esc_html($gateway->get_title()) . '</label>';
                echo '</div>';
            }
        }
        
        echo '</div>';
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function renderInstantOrderButton(): string
    {
        return sprintf(
            '<button type="submit" class="button instant-order-button" id="quick-place-order">%s</button>',
            __('Place Order Now', 'jankx-woocommerce')
        );
    }

    /**
     * {@inheritDoc}
     */
    public function renderProgressIndicator(): string
    {
        return '<div class="quick-checkout-progress"><span class="step active">1</span><span class="step">2</span><span class="step">3</span></div>';
    }

    /**
     * {@inheritDoc}
     */
    public function getTriggerType(): string
    {
        return $this->triggerType;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsOneClick(): bool
    {
        return $this->supportsOneClick;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsSavedAddresses(): bool
    {
        return $this->supportsSavedAddresses;
    }

    /**
     * {@inheritDoc}
     */
    public function getRequiredFields(): array
    {
        return $this->requiredFields;
    }

    /**
     * Set required fields
     *
     * @param array $fields
     * @return self
     */
    public function setRequiredFields(array $fields): self
    {
        $this->requiredFields = $fields;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function generateDynamicCss(array $settings): string
    {
        $css = '';

        if (isset($settings['modal_width'])) {
            $css .= sprintf(
                '.quick-checkout-%s { max-width: %dpx; }',
                $this->id,
                intval($settings['modal_width'])
            );
        }

        if (isset($settings['overlay_color'])) {
            $css .= sprintf(
                '.quick-checkout-%s-overlay { background-color: %s; }',
                $this->id,
                esc_attr($settings['overlay_color'])
            );
        }

        return $css;
    }
}

