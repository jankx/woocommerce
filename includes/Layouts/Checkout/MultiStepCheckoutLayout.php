<?php

namespace Jankx\WooCommerce\Layouts\Checkout;

use Jankx\WooCommerce\Abstracts\Layouts\AbstractCheckoutPageLayout;

/**
 * Class MultiStepCheckoutLayout
 * 
 * Multi-step checkout layout
 */
class MultiStepCheckoutLayout extends AbstractCheckoutPageLayout
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(
            'multistep-checkout',
            __('Multi-Step Checkout', 'jankx-woocommerce'),
            'checkout-page'
        );

        $this->checkoutLayout = 'multi-step';
        $this->isMultiStep = true;
        $this->stepsCount = 3;
        $this->priority = 20;

        $this->supportedSettings = [
            'steps_count',
            'show_progress_bar',
            'button_color',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function renderCheckoutPage(array $options = []): string
    {
        $template = $this->getTemplatePath();

        return $this->loadTemplate($template, [
            'layout' => $this,
            'steps' => $this->getSteps(),
            'progress_bar' => $this->renderProgressBar(),
        ]);
    }

    /**
     * Get checkout steps
     *
     * @return array
     */
    protected function getSteps(): array
    {
        return [
            [
                'id' => 'billing',
                'title' => __('Billing Details', 'jankx-woocommerce'),
                'content' => $this->renderBillingFields(),
            ],
            [
                'id' => 'shipping',
                'title' => __('Shipping Details', 'jankx-woocommerce'),
                'content' => $this->renderShippingFields(),
            ],
            [
                'id' => 'payment',
                'title' => __('Payment', 'jankx-woocommerce'),
                'content' => $this->renderPaymentMethods() . $this->renderOrderReview(),
            ],
        ];
    }

    /**
     * Render progress bar
     *
     * @return string
     */
    protected function renderProgressBar(): string
    {
        $steps = $this->getSteps();
        
        ob_start();
        echo '<div class="checkout-progress-bar">';
        
        foreach ($steps as $index => $step) {
            $stepNum = $index + 1;
            echo sprintf(
                '<div class="progress-step" data-step="%d">
                    <span class="step-number">%d</span>
                    <span class="step-title">%s</span>
                </div>',
                $stepNum,
                $stepNum,
                esc_html($step['title'])
            );
        }
        
        echo '</div>';
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    protected function generateDynamicCss(array $settings): string
    {
        $css = parent::generateDynamicCss($settings);

        if (isset($settings['show_progress_bar']) && !$settings['show_progress_bar']) {
            $css .= '.checkout-progress-bar { display: none; }';
        }

        $css .= '
            .checkout-steps .step-content {
                display: none;
            }
            .checkout-steps .step-content.active {
                display: block;
            }
        ';

        return $css;
    }
}

