<?php
namespace Jankx\Ecommerce\Base\Component;

use WC_Cart;
use WC_Session_Handler;
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

    public function parseProps($props)
    {
        $eCommerce   = Ecommerce::instance();
        static::$shopPlugin = $eCommerce->getShopPlugin();
        $this->props = wp_parse_args($props, array(
            'show_badge' => true,
            'text' => null,
            'cart_url' => static::$shopPlugin->getCartUrl(),
            'preview' => false,
            'icon' => '<span class="dashicons-cart"></span>',
            'preview_content' => 'components/cart/cart_preview',
        ));
    }

    public function render()
    {
        global $woocommerce;

        if (is_null($woocommerce->session)) {
            $woocommerce->session = new WC_Session_Handler();
            $woocommerce->session->init();
        }

        if (is_null($woocommerce->cart)) {
            $woocommerce->cart = new WC_Cart();
        }

        $output  = sprintf('<div class="jankx-ecommerce cart-icon">');
        $output .= EcommerceTemplate::render(
            'components/cart/cart_button_link',
            array_merge($this->props, array(
                'badge' => $woocommerce->cart->get_cart_contents_count(),
            )),
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
