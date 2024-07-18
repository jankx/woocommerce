<?php

namespace Jankx\WooCommerce\Widget;

use WP_Widget;
use Jankx;

class ProductAttributes extends WP_Widget
{
    protected $customTabTitle;

    public function __construct()
    {
        parent::__construct(
            'jankx-ecom-product-attribute',
            Jankx::templateName() . ' ' . __('Product Attributes'),
            array(
                'classname' => 'jankx-ecom-prod-attributes',
            )
        );
    }

    public function form($instance)
    {
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php echo esc_html(__('Title')); ?></label>
            <input
                type="text"
                class="widefat"
                id="<?php echo $this->get_field_id('title'); ?>"
                name="<?php echo $this->get_field_name('title'); ?>"
                value="<?php echo array_get($instance, 'title'); ?>"
            />
        </p>
        <?php
    }

    public function widget($args, $instance)
    {
        ob_start();
        woocommerce_product_additional_information_tab();
        $attributes_html = ob_get_clean();

        if (empty($attributes_html)) {
            return;
        }

        echo $args['before_widget'];
        if (!empty($instance['title'])) {
            $this->customTabTitle = function () use ($instance) {
                return $instance['title'];
            };
            add_filter('woocommerce_product_additional_information_heading', $this->customTabTitle);
        }

        echo $attributes_html;

        if ($this->customTabTitle) {
            remove_filter('woocommerce_product_additional_information_heading', $this->customTabTitle);
        }
        echo $args['after_widget'];
    }
}