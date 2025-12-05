<?php

namespace Jankx\WooCommerce\Abstracts\Layouts;

use Jankx\WooCommerce\Abstracts\AbstractLayout;
use Jankx\WooCommerce\Contracts\Layouts\CartFormLayoutInterface;

/**
 * Abstract Class AbstractCartFormLayout
 * 
 * Base implementation cho Cart Form layouts (mini cart, cart widget)
 */
abstract class AbstractCartFormLayout extends AbstractLayout implements CartFormLayoutInterface
{
    /**
     * Layout type
     *
     * @var string
     */
    protected $type = 'cart-form';

    /**
     * Show thumbnails
     *
     * @var bool
     */
    protected $showThumbnails = true;

    /**
     * Editable cart
     *
     * @var bool
     */
    protected $isEditable = true;

    /**
     * {@inheritDoc}
     */
    public function render(array $data = []): string
    {
        $cartItems = $data['cart_items'] ?? WC()->cart->get_cart();
        $options = $data['options'] ?? [];

        return $this->renderCartForm($cartItems, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function renderCartForm(array $cartItems, array $options = []): string
    {
        $template = $this->getTemplatePath();

        ob_start();
        echo '<div class="jankx-cart-form ' . esc_attr($this->getId()) . '">';
        
        if (!empty($cartItems)) {
            echo '<div class="cart-items">';
            foreach ($cartItems as $cart_item_key => $cart_item) {
                echo $this->renderCartItem($cart_item);
            }
            echo '</div>';
            
            echo $this->renderCartTotals();
        } else {
            echo '<p class="cart-empty">' . __('Your cart is empty.', 'jankx-woocommerce') . '</p>';
        }
        
        echo '</div>';
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function renderCartItem(array $cartItem): string
    {
        $_product = $cartItem['data'];
        
        if (!$_product || !$_product->exists()) {
            return '';
        }

        ob_start();
        echo '<div class="cart-item" data-cart-item-key="' . esc_attr($cartItem['key'] ?? '') . '">';
        
        if ($this->showThumbnails) {
            echo $this->renderItemThumbnail($cartItem);
        }
        
        echo '<div class="item-content">';
        echo $this->renderItemDetails($cartItem);
        echo $this->renderItemPrice($cartItem);
        
        if ($this->isEditable) {
            echo $this->renderQuantitySelector($cartItem);
        }
        echo '</div>';
        
        if ($this->isEditable) {
            echo $this->renderRemoveButton($cartItem);
        }
        
        echo '</div>';
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function renderItemThumbnail(array $cartItem): string
    {
        $_product = $cartItem['data'];
        $thumbnail = $_product->get_image('thumbnail');
        
        return sprintf(
            '<div class="item-thumbnail"><a href="%s">%s</a></div>',
            esc_url($_product->get_permalink()),
            $thumbnail
        );
    }

    /**
     * {@inheritDoc}
     */
    public function renderItemDetails(array $cartItem): string
    {
        $_product = $cartItem['data'];
        
        return sprintf(
            '<div class="item-details"><a href="%s" class="item-name">%s</a></div>',
            esc_url($_product->get_permalink()),
            esc_html($_product->get_name())
        );
    }

    /**
     * {@inheritDoc}
     */
    public function renderQuantitySelector(array $cartItem): string
    {
        $_product = $cartItem['data'];
        $quantity = $cartItem['quantity'] ?? 1;
        
        return sprintf(
            '<div class="quantity-selector">
                <button type="button" class="qty-minus">-</button>
                <input type="number" class="qty-input" value="%d" min="1" max="%d" />
                <button type="button" class="qty-plus">+</button>
            </div>',
            $quantity,
            $_product->get_max_purchase_quantity()
        );
    }

    /**
     * {@inheritDoc}
     */
    public function renderItemPrice(array $cartItem): string
    {
        return sprintf(
            '<div class="item-price">%s</div>',
            WC()->cart->get_product_subtotal($cartItem['data'], $cartItem['quantity'])
        );
    }

    /**
     * {@inheritDoc}
     */
    public function renderRemoveButton(array $cartItem): string
    {
        return sprintf(
            '<button type="button" class="remove-item" data-cart-item-key="%s">&times;</button>',
            esc_attr($cartItem['key'] ?? '')
        );
    }

    /**
     * {@inheritDoc}
     */
    public function renderCartTotals(): string
    {
        ob_start();
        echo '<div class="cart-totals">';
        echo '<div class="subtotal">';
        echo '<span>' . __('Subtotal:', 'jankx-woocommerce') . '</span>';
        echo '<span>' . WC()->cart->get_cart_subtotal() . '</span>';
        echo '</div>';
        echo '</div>';
        return ob_get_clean();
    }

    /**
     * {@inheritDoc}
     */
    public function showThumbnails(): bool
    {
        return $this->showThumbnails;
    }

    /**
     * {@inheritDoc}
     */
    public function isEditable(): bool
    {
        return $this->isEditable;
    }

    /**
     * {@inheritDoc}
     */
    protected function generateDynamicCss(array $settings): string
    {
        $css = '';

        if (isset($settings['thumbnail_size'])) {
            $css .= sprintf(
                '.cart-form-%s .item-thumbnail { width: %dpx; }',
                $this->id,
                intval($settings['thumbnail_size'])
            );
        }

        return $css;
    }
}

