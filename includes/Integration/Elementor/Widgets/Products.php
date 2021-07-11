<?php
namespace Jankx\Ecommerce\Integration\Elementor\Widgets;

use Jankx;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Jankx\Ecommerce\Base\Renderer\ProductsRenderer;
use Jankx\PostLayout\PostLayoutManager;
use Jankx\PostLayout\Layout\Card;

class Products extends Widget_Base
{
    public function get_name()
    {
        return 'jankx_ecommerce_products';
    }

    public function get_title()
    {
        return sprintf(
            __('%s Products', 'jankx'),
            Jankx::templateName()
        );
    }

    public function get_icon()
    {
        return 'eicon-products';
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

        $taxQuery = array('taxonomy' => 'product_cat', 'fields' => 'id=>name', 'hide_empty' => false);
        $productCats = version_compare($GLOBALS['wp_version'], '4.5.0') >= 0
            ? get_terms($taxQuery)
            : get_terms($taxQuery['taxonomy'], $taxQuery);

        $taxQuery = array('taxonomy' => 'product_tag', 'fields' => 'id=>name', 'hide_empty' => false);
        $productTags = version_compare($GLOBALS['wp_version'], '4.5.0') >= 0
            ? get_terms($taxQuery)
            : get_terms($taxQuery['taxonomy'], $taxQuery);

        $postLayoutManager = PostLayoutManager::getInstance();


        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'jankx_ecommerce'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'title',
            [
                'label' => __('Widget Title', 'jankx_ecommerce'),
                'type' => Controls_Manager::TEXT,
            ]
        );

        $this->add_control(
            'product_categories',
            [
                'label' => __('Product Categories', 'jankx_ecommerce'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $productCats,
                'default' => '',
            ]
        );


        $this->add_control(
            'product_tags',
            [
                'label' => __('Product Tags', 'jankx_ecommerce'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $productTags,
                'default' => 'none',
            ]
        );
        $this->add_control(
            'layout',
            [
                'label' => __('Layout', 'jankx_ecommerce'),
                'type' => Controls_Manager::SELECT,
                'multiple' => true,
                'options' => $postLayoutManager->getLayouts(array(
                    'type' => 'names'
                )),
                'default' => Card::LAYOUT_NAME,
            ]
        );


        $this->add_control(
            'limit',
            [
                'label' => __('Number of Products to show', 'jankx_ecommerce'),
                'type' => Controls_Manager::NUMBER,
                'max' => 100,
                'step' => 1,
                'default' => 10,
            ]
        );

        $this->add_control(
            'items_per_row',
            [
                'label' => __('Number items per row', 'jankx_ecommerce'),
                'type' => Controls_Manager::NUMBER,
                'max' => 6,
                'step' => 1,
                'default' => 4,
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $productsModule = new ProductsRenderer(array(
            'limit' => array_get($settings, 'limit', 10),
            'items_per_row' => array_get($settings, 'items_per_row', 4),
            'widget_title' => array_get($settings, 'title', 10),
            'categories' => array_get($settings, 'product_categories', array()),
            'tags' => array_get($settings, 'product_tags', array()),
            'layout' => array_get($settings, 'layout'),
        ));
        if (($url = array_get($settings, 'readmore_url', ''))) {
            $productsModule->setReadMore($url);
        }

        // Render the content
        echo $productsModule;
    }

    protected function _content_template()
    {
    }
}
