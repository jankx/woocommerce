<?php

namespace Jankx\WooCommerce\Renderer;

use Jankx\MobileLayout\LayoutManager;
use Jankx\WooCommerce\Constracts\Renderer;
use Jankx\WooCommerce\WooCommerce;
use Jankx\WooCommerce\WooCommerceTemplate;
use Jankx\WooCommerce\GetProductQuery;
use Jankx\PostLayout\PostLayoutManager;
use Jankx\PostLayout\Layout\Card;
use Jankx\TemplateAndLayout;
use Jankx\Widget\Renderers\Base as RendererBase;

class ProductsRenderer extends RendererBase
{
    protected static $supportedFirstTabs;

    protected $categoryIds = array();
    protected $readmore = array();
    protected $args;
    protected $layoutOptions = array();
    protected $wp_query;

    public function __construct($args = array())
    {
        $this->args     = $args;

        if (is_null(static::$supportedFirstTabs)) {
            static::$supportedFirstTabs = apply_filters(
                'jankx_woocommerce_category_tabs_products_first_tabs',
                array(
                    'featured' => __('Featured', 'jankx'),
                    'recents' => __('Recents', 'jankx'),
                )
            );
        }
    }

    public function __toString()
    {
        return (string) $this->render();
    }

    public function setReadMore($url, $text = null)
    {
        if (is_null($text)) {
            $text = __('View all', 'jankx');
        }

        $this->readmore = array(
            'text' => $text,
            'url' => $url
        );
    }

    public function buildFirstTabQuery()
    {
        $productQuery = GetProductQuery::buildQuery(array(
            'post_type' => 'product',
            'categories' => array_get($this->args, 'categories'),
            'limit' => array_get($this->args, 'limit'),
            'type' => array_get($this->args, 'type')
        ));

        return $productQuery->getWordPressQuery();
    }


    public function render()
    {
        $wp_query   = empty($this->wp_query) ? $this->buildFirstTabQuery() : $this->wp_query;
        $plugin = jankx_woocommerce()->getShopPlugin();
        $postLayoutManager = PostLayoutManager::getInstance(
            TemplateAndLayout::getTemplateEngine()->getId()
        );

        if (is_null($wp_query)) {
            return __('The products not found', 'jankx');
        }

        do_action("jankx/woocommerce/loop/before", $this->args);

        $layout             = array_get($this->args, 'layout', Card::LAYOUT_NAME);
        $loopItemLayoutType = array_get($this->args, 'item_layout', WooCommerce::instance()->getDefaultLoopItemLayout());
        $loopItemLayout     = $postLayoutManager->getLoopItemContentByType($loopItemLayoutType);

        // Create post layout
        $postLayout = $postLayoutManager->createLayout(
            $layout,
            $wp_query,
            $loopItemLayout
        );

        add_filter('single_product_archive_thumbnail_size', [$this, 'changeThumbnailSize']);
        add_filter('woocommerce_product_get_image', [$this, 'createImageWrapper'], 10, 4);

        $postLayout->setOptions($this->getLayoutOptions());
        $postLayout->setContentGenerator(
            $plugin->getContentGenerator()
        );
        $content = $postLayout->render(false);


        do_action("jankx/woocommerce/loop/end", $content, $this->args);

        remove_filter('woocommerce_product_get_image', [$this, 'createImageWrapper'], 10, 4);
        remove_filter('single_product_archive_thumbnail_size', [$this, 'changeThumbnailSize']);

        return $content;
    }

    public function changeThumbnailSize($size)
    {
        if (($optionSize = $this->getLayoutOption('thumbnail_size')) !== 'woocommerce_thumbnail') {
            if ($optionSize !== 'custom') {
                return $optionSize;
            }
            return sprintf('%sx%s', $this->getLayoutOption('image_width', 300), $this->getLayoutOption('image_height', 300));
        }
        return $size;
    }

    public function createImageWrapper($image, $wc_product, $size, $attr)
    {
        return jankx_template('post-layout/thumbnail', [
            'post' => $wc_product,
            'data_index' => 0,
            'thumbnail_size' => $size,
            'content' => $image
        ], false);
    }


    public function setMainQuery($wp_query)
    {
        $this->wp_query = &$wp_query;
    }
}
