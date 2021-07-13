<?php
namespace Jankx\Ecommerce\Integration\Elementor\Widgets;

use Jankx;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Jankx\Ecommerce\Base\Renderer\CategoryTabsProductsRenderer;
use Jankx\PostLayout\PostLayoutManager;
use Jankx\PostLayout\Layout\Card;

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
        return array(
            'woocommerce-elements',
            Jankx::templateStylesheet()
        );
    }

    protected function _register_controls()
    {
        global $wp_version;
        $args = array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'fields' => 'id=>name',
        );

        $product_categories = version_compare($wp_version, '4.5')
            ? get_terms($args) :
            get_terms($args['taxonomy'], $args);

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

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'list_title',
            [
                'label' => __('Title', 'jankx_ecommerce'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'category',
            [
                'label' => __('Category', 'jankx_ecommerce'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'solid',
                'options' => $product_categories,
            ]
        );

        $this->add_control(
            'categories',
            [
                'label' => __('Categories', 'jankx_ecommerce'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [],
                'title_field' => '{{{ list_title }}}',
            ]
        );

        $this->add_control(
            'sub_layout',
            [
                'label' => __('Layout', 'jankx_ecommerce'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => Card::LAYOUT_NAME,
                'options' => PostLayoutManager::getLayouts(array(
                    'field' => 'names',
                    'type' => 'children'
                )),
            ]
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

    protected function makeRendererTabs($datas)
    {
        $categories = array();
        if (!is_array($datas)) {
            return $categories;
        }

        foreach ($datas as $data) {
            $categories[$data['category']] = array_get($data, 'list_title');
        }
        return $categories;
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $categories = array_get($settings, 'categories', array());

        if (empty($categories)) {
            return;
        }

        $firstTag = array_get($settings, 'first_tab', 'feature');
        if (!array_get($settings, 'show_first_tab', 'no') === 'no') {
            $firstTag = null;
        }

        $categoryTabsProducts = new CategoryTabsProductsRenderer($this->makeRendererTabs($categories), $firstTag, array(
            'limit' => array_get($settings, 'limit', 10),
            'items_per_row' => array_get($settings, 'posts_per_row', 4),
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
