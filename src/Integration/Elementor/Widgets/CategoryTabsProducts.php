<?php
namespace Jankx\Ecommerce\Integration\Elementor\Widgets;

use Elementor\Widget_Base;

class CategoryTabsProducts extends Widget_Base
{
    public function get_name()
    {
        return 'jankx_ecommerce_category_tab_products';
    }

    public function get_title()
    {
        return __('Category Tabs Products', 'jankx');
    }

    public function get_icon()
    {
        return 'eicon-product-tabs';
    }

    public function get_categories()
    {
        return array('woocommerce-elements');
    }

    protected function _register_controls()
    {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'plugin-name'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'text_align',
            [
                'label' => __('Alignment', 'plugin-domain'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'plugin-domain'),
                        'icon' => 'fa fa-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'plugin-domain'),
                        'icon' => 'fa fa-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'plugin-domain'),
                        'icon' => 'fa fa-align-right',
                    ],
                ],
                'default' => 'center',
                'toggle' => true,
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
    }

    protected function _content_template()
    {
    }
}
