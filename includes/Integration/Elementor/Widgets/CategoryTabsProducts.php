<?php
namespace Jankx\Ecommerce\Integration\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Jankx\Ecommerce\Base\Modules\CategoryTabsProductsModule;

class CategoryTabsProducts extends Widget_Base
{
    public function get_name()
    {
        return 'jankx_ecommerce_category_tab_products';
    }

    public function get_title()
    {
        return __('Category Tabs Products', 'jankx_ecommerce');
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
            array(
                'label' => __('Content', 'jankx_ecommerce'),
                'tab' => Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'title',
            array(
                'label' => __('Widget Title', 'jankx_ecommerce'),
                'type' => Controls_Manager::TEXT,
            )
        );

        $this->add_control(
            'show_first_tab',
            array(
                'label' => __('Show First Tab', 'jankx_ecommerce'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'jankx_ecommerce'),
                'label_off' => __('Hide', 'jankx_ecommerce'),
                'return_value' => 'yes',
                'default' => 'no',
            )
        );

        $this->add_control(
            'first_tab',
            array(
                'label' => __('Choose First Tab', 'jankx_ecommerce'),
                'type' => Controls_Manager::SELECT,
                'default' => 'solid',
                'options' => array(
                    'featured'  => __('Featured', 'jankx_ecommerce'),
                    'recents'  => __('Recents', 'jankx_ecommerce'),
                ),
                'of_type' => 'show_first_tab',
                'condition' => array(
                    'show_first_tab' => 'yes',
                ),
            )
        );

        $this->add_control(
            'category',
            array(
                'label' => __('Category IDs', 'jankx_ecommerce'),
                'type' => Controls_Manager::TEXT,
                'default' => __('0', 'jankx_ecommerce'),
                'placeholder' => __('Input your product categories to here', 'jankx_ecommerce'),
            )
        );

        $this->add_control(
            'limit',
            array(
                'label' => __('Number of Products', 'jankx_ecommerce'),
                'type' => Controls_Manager::NUMBER,
                'max' => 100,
                'step' => 1,
                'default' => 10,
            )
        );

        $this->add_control(
            'posts_per_row',
            array(
                'label' => __('Row Items', 'jankx_ecommerce'),
                'type' => Controls_Manager::NUMBER,
                'max' => 10,
                'step' => 1,
                'default' => 4,
            )
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
        $categoryTabsProducts = new CategoryTabsProductsModule($categoryIds, $firstTag, array(
            'limit' => array_get($settings, 'limit', 10),
            'row_items' => array_get($settings, 'posts_per_row', 4),
            'widget_title' => array_get($settings, 'title', 10),
        ));
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
