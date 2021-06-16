<?php
namespace Jankx\Ecommerce\Base\Component;

use Jankx\Component\Abstracts\Component;
use Jankx\Component\Components\Icon;
use Jankx\Ecommerce\EcommerceTemplate;
use Jankx\Ecommerce\Ecommerce;
use Jankx\Component\Template as TemplateComponent;

class CartButton extends Component
{
    const COMPONENT_NAME = 'cart_button';

    protected static $cartContentRendered = false;
    protected static $shopPlugin;

    public function getName()
    {
        return static::COMPONENT_NAME;
    }

    protected function parseProps($props)
    {
        $eCommerce   = Ecommerce::instance();
        static::$shopPlugin = $eCommerce->getShopPlugin();
        $this->props = wp_parse_args($props, array(
            'show_badge' => true,
            'icon' => array(
                'name' => 'cart',
                'font' => 'material',
            ),
            'text' => null,
            'cart_url' => static::$shopPlugin->getCartUrl(),
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
        global $woocommerce;
        $this->props['icon'] = $this->buildIcon($this->props['icon']);

        $output  = sprintf('<div class="jankx-ecommerce cart-icon">');
        $output .= EcommerceTemplate::render(
            'components/cart/cart_button_link',
            array_merge($this->props, array(
                'badge' => $woocommerce->cart->get_cart_contents_count(),
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

        if (!static::$cartContentRendered) {
            add_action('wp_footer', array($this, 'renderCartContentInFooter'));
            static::$cartContentRendered = true;
        }
        return $output;
    }

    public function renderCartContentInFooter()
    {
        ?>
        <script type="text/x-tmpl" id="jankx-ecommerce-cart-content">
            <?php echo static::$shopPlugin->getCartContent(); ?>
        </script>
        <?php
    }
}
