<?php
namespace Jankx\WooCommerce\Integration\Elementor\Widgets;

use Jankx;
use Jankx\Elementor\WidgetBase;
use Elementor\Controls_Manager;
use Jankx\WooCommerce\Renderer\ProductsRenderer;
use Jankx\PostLayout\PostLayoutManager;
use Jankx\PostLayout\Layout\Card;

class Products extends WidgetBase
{
    public function get_name()
    {
        return 'jankx_woocommerce_products';
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

    protected function register_controls()
    {

        $taxQuery = array('taxonomy' => 'product_cat', 'fields' => 'id=>name', 'hide_empty' => false);
        $productCats = version_compare($GLOBALS['wp_version'], '4.5.0') >= 0
            ? get_terms($taxQuery)
            : get_terms($taxQuery['taxonomy'], $taxQuery);

        $taxQuery = array('taxonomy' => 'product_tag', 'fields' => 'id=>name', 'hide_empty' => false);
        $productTags = version_compare($GLOBALS['wp_version'], '4.5.0') >= 0
            ? get_terms($taxQuery)
            : get_terms($taxQuery['taxonomy'], $taxQuery);


        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'jankx_woocommerce'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'title',
            [
                'label' => __('Widget Title', 'jankx_woocommerce'),
                'type' => Controls_Manager::TEXT,
            ]
        );

        $this->add_control(
            'product_categories',
            [
                'label' => __('Product Categories', 'jankx_woocommerce'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $productCats,
                'default' => '',
            ]
        );


        $this->add_control(
            'product_tags',
            [
                'label' => __('Product Tags', 'jankx_woocommerce'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $productTags,
                'default' => 'none',
            ]
        );
        $this->add_responsive_control(
            'layout',
            [
                'label' => __('Layout', 'jankx_woocommerce'),
                'type' => Controls_Manager::SELECT,
                'multiple' => true,
                'options' => PostLayoutManager::getLayouts(array(
                    'field' => 'names'
                )),
                'default' => Card::LAYOUT_NAME,
            ]
        );

        $this->addThumbnailControls();


        $this->add_responsive_control(
            'limit',
            [
                'label' => __('Number of Products to show', 'jankx_woocommerce'),
                'type' => Controls_Manager::NUMBER,
                'max' => 100,
                'step' => 1,
                'default' => 10,
            ]
        );

        $this->add_responsive_control(
            'columns',
            [
                'label' => __('Columns', 'jankx_woocommerce'),
                'type' => Controls_Manager::NUMBER,
                'max' => 6,
                'step' => 1,
                'default' => 4,
            ]
        );


        $this->add_responsive_control(
            'rows',
            [
                'label' => __('Rows', 'jankx_woocommerce'),
                'type' => Controls_Manager::NUMBER,
                'max' => 6,
                'step' => 1,
                'default' => 1,
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $productsModule = new ProductsRenderer(array(
            'widget_title' => array_get($settings, 'title', 10),
            'categories' => array_get($settings, 'product_categories', array()),
            'tags' => array_get($settings, 'product_tags', array()),
            'layout' => $this->get_responsive_setting('layout', Card::LAYOUT_NAME),
            'limit' => $this->get_responsive_setting('limit', 10),
        ));
        if (($url = array_get($settings, 'readmore_url', ''))) {
            $productsModule->setReadMore($url);
        }

        $productsModule->setLayoutOptions(array(
            'columns_tablet' => array_get($settings, 'columns_tablet', 2),
            'columns_mobile' => array_get($settings, 'columns', 1),
            'columns' => $this->get_responsive_setting('columns', 4),
            'rows' => $this->get_responsive_setting('rows', 1),
            'thumbnail_size'  => array_get($settings, 'thumbnail_size', 'medium'),
            'wrap_tag_name' => 'ul',
        ));

        // Set Woocommerce loop columns
        wc_get_loop_prop('columns', $this->get_responsive_setting('columns', 4));

        // Render the content
        $productsContent = $productsModule->render(false);
        $widgetTitle = array_get($settings, 'title');
        if ($widgetTitle && $productsContent) {
            echo sprintf('<h3 class="products-widget-title"><span>%s</span></h3>', $widgetTitle);
        }
        echo $productsContent;
    }
}
