<?php

namespace Jankx\WooCommerce\Abstracts\Layouts;

use Jankx\WooCommerce\Abstracts\AbstractLayout;
use Jankx\WooCommerce\Contracts\Layouts\CartPageLayoutInterface;

/**
 * Abstract Class AbstractCartPageLayout
 * 
 * Base implementation cho Cart Page layouts
 */
abstract class AbstractCartPageLayout extends AbstractLayout implements CartPageLayoutInterface
{
    /**
     * Layout type
     *
     * @var string
     */
    protected $type = 'cart-page';

    /**
     * Page layout
     *
     * @var string
     */
    protected $pageLayout = 'fullwidth'; // fullwidth, sidebar

    /**
     * Show cross-sells
     *
     * @var bool
     */
    protected $showCrossSells = true;

    /**
     * Show shipping calculator
     *
     * @var bool
     */
    protected $showShippingCalculator = true;

    /**
     * {@inheritDoc}
     */
    public function render(array $data = []): string
    {
        return $this->renderCartPage($data['options'] ?? []);
    }

    /**
     * {@inheritDoc}
     */
    public function renderCartPage(array $options = []): string
    {
        $template = $this->getTemplatePath();

        return $this->loadTemplate($template, [
            'layout' => $this,
            'cart_table' => $this->renderCartTable(),
            'cart_actions' => $this->renderCartActions(),
            'cross_sells' => $this->showCrossSells ? $this->renderCrossSells() : '',
            'collaterals' => $this->renderCartCollaterals(),
            'coupon_form' => $this->renderCouponForm(),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function renderCartTable(): string
    {
        ob_start();
        woocommerce_cart_table();
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function renderCartActions(): string
    {
        ob_start();
        ?>
        <div class="cart-actions">
            <button type="submit" class="button" name="update_cart" value="<?php esc_attr_e('Update cart', 'jankx-woocommerce'); ?>">
                <?php esc_html_e('Update cart', 'jankx-woocommerce'); ?>
            </button>
            <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="button">
                <?php esc_html_e('Continue shopping', 'jankx-woocommerce'); ?>
            </a>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function renderCrossSells(): string
    {
        ob_start();
        woocommerce_cross_sell_display();
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function renderCartCollaterals(): string
    {
        ob_start();
        echo '<div class="cart-collaterals">';
        woocommerce_cart_totals();
        echo '</div>';
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function renderCouponForm(): string
    {
        ob_start();
        woocommerce_cart_coupon();
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function renderShippingCalculator(): string
    {
        if (!$this->showShippingCalculator) {
            return '';
        }

        ob_start();
        woocommerce_shipping_calculator();
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function renderCheckoutButton(): string
    {
        return sprintf(
            '<a href="%s" class="checkout-button button alt">%s</a>',
            esc_url(wc_get_checkout_url()),
            __('Proceed to checkout', 'jankx-woocommerce')
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getPageLayout(): string
    {
        return $this->pageLayout;
    }

    /**
     * {@inheritDoc}
     */
    public function showCrossSells(): bool
    {
        return $this->showCrossSells;
    }

    /**
     * {@inheritDoc}
     */
    public function showShippingCalculator(): bool
    {
        return $this->showShippingCalculator;
    }

    /**
     * {@inheritDoc}
     */
    protected function generateDynamicCss(array $settings): string
    {
        $css = '';

        if (isset($settings['table_border_color'])) {
            $css .= sprintf(
                '.cart-page-%s .cart-table { border-color: %s; }',
                $this->id,
                esc_attr($settings['table_border_color'])
            );
        }

        return $css;
    }
}

