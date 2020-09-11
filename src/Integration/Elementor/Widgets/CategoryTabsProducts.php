<?php
namespace Jankx\Ecommerce\Integration\Elementor\Widgets;

use Elementor\Widget_Base;
use Jankx\Ecommerce\Base\CategoryTabsProducts as BaseCategoryTabsProducts;

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
            'show_first_tab',
            [
                'label' => __('Show First Tab', 'jankx'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'jankx'),
                'label_off' => __('Hide', 'jankx'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->add_control(
            'first_tab',
            [
                'label' => __('Choose First Tab', 'jankx'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'solid',
                'options' => [
                    'featured'  => __('Featured', 'jankx'),
                    'recents'  => __('Recents', 'jankx'),
                ],
                'of_type' => 'show_first_tab',
                'condition' => [
                    'show_first_tab' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'category',
            [
                'label' => __('Category IDs', 'jankx'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('0', 'jankx'),
                'placeholder' => __('Input your product categories to here', 'jankx'),
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $categoryIds = explode(',', array_get($settings, 'category', ''));
        $firstTag = array_get($settings, 'first_tab', 'feature');
        if (!array_get($settings, 'show_first_tab', 'no') === 'no') {
            $firstTag = null;
        }
        $categoryTabsProducts = new BaseCategoryTabsProducts($categoryIds, $firstTag);
        if (($url = array_get($settings, 'readmore_url', ''))) {
            $categoryTabsProducts->setReadMore($url);
        }

        // Render the content
        echo $categoryTabsProducts;
    }

    protected function _content_template()
    {
    }
}
