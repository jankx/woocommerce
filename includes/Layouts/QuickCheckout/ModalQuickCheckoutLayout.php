<?php

namespace Jankx\WooCommerce\Layouts\QuickCheckout;

use Jankx\WooCommerce\Abstracts\Layouts\AbstractQuickCheckoutLayout;

/**
 * Class ModalQuickCheckoutLayout
 * 
 * Modal quick checkout layout - Default
 */
class ModalQuickCheckoutLayout extends AbstractQuickCheckoutLayout
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(
            'modal-quick-checkout',
            __('Modal Quick Checkout', 'jankx-woocommerce'),
            'quick-checkout'
        );

        $this->triggerType = 'modal';
        $this->supportsOneClick = true;
        $this->supportsSavedAddresses = true;
        $this->priority = 10;

        $this->supportedSettings = [
            'modal_width',
            'overlay_color',
            'enable_one_click',
            'show_progress',
        ];

        $this->requiredFields = [
            'billing_phone',
            'billing_email',
            'billing_address_1',
            'billing_city',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function generateDynamicCss(array $settings): string
    {
        $css = parent::generateDynamicCss($settings);

        $css .= '
            .quick-checkout-modal {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                z-index: 9999;
                padding: 30px;
            }
            .quick-checkout-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 9998;
            }
        ';

        if (isset($settings['show_progress']) && !$settings['show_progress']) {
            $css .= '.quick-checkout-progress { display: none; }';
        }

        return $css;
    }
}

