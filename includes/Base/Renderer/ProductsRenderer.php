<?php
namespace Jankx\Ecommerce\Base\Renderer;

use Jankx\Ecommerce\Constracts\Renderer;
use Jankx\Ecommerce\Ecommerce;
use Jankx\Ecommerce\EcommerceTemplate;
use Jankx\Ecommerce\Base\GetProductQuery;
use Jankx\PostLayout\PostLayoutManager;
use Jankx\PostLayout\Layout\Card;
use Jankx\TemplateLoader;
use Jankx\Widget\Renderers\Base as RendererBase;

class ProductsRenderer extends RendererBase
{
    protected static $supportedFirstTabs;

    protected $categoryIds = array();
    protected $readmore = array();
    protected $args;

    public function __construct($args = array())
    {
        $this->args     = $args;

        if (is_null(static::$supportedFirstTabs)) {
            static::$supportedFirstTabs = apply_filters(
                'jankx_ecommerce_category_tabs_products_first_tabs',
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
        ));

        return $productQuery->getWordPressQuery();
    }


    public function render()
    {
        $wp_query   = $this->buildFirstTabQuery();
        $plugin = jankx_ecommerce()->getShopPlugin();
        $postLayoutManager = PostLayoutManager::getInstance(
            TemplateLoader::getTemplateEngine()->getId()
        );

        if (is_null($wp_query)) {
            return __('The products not found', 'jankx');
        }

        do_action("jankx/ecommerce/loop/before", $this->args);

        $layout = array_get($this->args, 'layout', Card::LAYOUT_NAME);
        $postLayout = $postLayoutManager->createLayout($layout, $wp_query);
        $postLayout->setOptions($this->getLayoutOptions());

        $postLayout->setContentGenerator(
            $plugin->getContentGenerator()
        );
        return $postLayout->render(false);
    }
}
