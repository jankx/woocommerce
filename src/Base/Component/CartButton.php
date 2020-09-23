<?php
namespace Jankx\Ecommerce\Base\Component;

use Jankx\Component\Abstracts\Component;
use Jankx\Component\Icon;
use Jankx\Ecommerce\EcommerceTemplate;
use Jankx\Ecommerce\Ecommerce;
use Jankx\Component\Template as TemplateComponent;

class CartButton extends Component
{
    public static function getName()
    {
        return 'cart_button';
    }

    protected function parseProps($props)
    {
        $eCommerce   = Ecommerce::instance();
        $this->props = wp_parse_args($props, array(
            'show_badge' => true,
            'icon' => array(
                'name' => 'cart',
                'font' => 'material',
            ),
            'text' => null,
            'cart_url' => $eCommerce->getShopPlugin()->getCartUrl(),
            'preview' => false,
            'preview_content' => 'components/cart/cart_preview',
        ));
    }

    protected function buildIcon($icon)
    {
        if (is_a($icon, Icon::class)) {
            return $icon;
        }
        if (is_array($icon) && !empty($icon['name'])) {
            return new Icon(array(
                'name' => $icon['name'],
                'font' => array_get($icon, 'font', 'material')
            ));
        }
        return new Icon(array(
            'name' => $icon,
        ));
    }

    public function render()
    {
        $this->props['icon'] = $this->buildIcon($this->props['icon']);

        $output  = sprintf('<div id="jankx-cart-icon" class="ecommerce-cart">');
        $output .= EcommerceTemplate::render(
            'components/cart/cart_button_link',
            array_merge($this->props, array(
                'badge' => 0,
            )),
            'ecommerce_cart_button',
            false
        );
        if ($this->props['preview']) {
            if (is_string($this->props['preview_content'])) {
                $this->props['preview_content'] = new TemplateComponent(array(
                    'template' => $this->props['preview_content'],
                    'data'     => apply_filters('jankx_ecommerce_cart_preview_data', array(
                        'cart_url' => $this->props['cart_url'],
                    ))
                ));
            }
            $output .= $this->props['preview_content'];
        }
        $output .= '</div>';

        return $output;
    }
}
