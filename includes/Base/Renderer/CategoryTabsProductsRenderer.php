<?php
namespace Jankx\Ecommerce\Base\Renderer;

use Jankx\Ecommerce\Constracts\Renderer;
use Jankx\Ecommerce\Ecommerce;
use Jankx\Ecommerce\EcommerceTemplate;
use Jankx\Ecommerce\Base\GetProductQuery;
use Jankx\Ecommerce\Base\TemplateManager;
use Jankx\TemplateLoader;
use Jankx\PostLayout\PostLayoutManager;
use Jankx\PostLayout\Layout\Card;

class CategoryTabsProductsRenderer implements Renderer
{
    protected static $supportedFirstTabs;
    protected static $templateIsCreated;

    protected $categories = array();
    protected $readmore = array();
    protected $firstTab;
    protected $tabs;
    protected $args;

    public function __construct($categories, $firstTab = null, $args = array())
    {
        $this->categories = $categories;
        $this->firstTab = $firstTab;
        $this->args     = $args;

        if (is_null(static::$supportedFirstTabs)) {
            static::$supportedFirstTabs = apply_filters(
                'jankx_ecommerce_category_tabs_products_first_tabs',
                array(
                    'featured' => __('Featured'),
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

    public function generateTabs($type = 'category')
    {
        $this->tabs = [];
        if ($this->firstTab && isset(static::$supportedFirstTabs[$this->firstTab])) {
            $this->tabs[static::$supportedFirstTabs[$this->firstTab]] = array(
                'tab' => $this->firstTab,
                'url' => '#',
                'type' => 'special'
            );
        }
        $taxonomy = jankx_ecommerce()->getShopPlugin()->getProductCategoryTaxonomy();

        foreach ($this->categories as $categoryId => $tabTitle) {
            $term = get_term($categoryId, $taxonomy);
            if (is_null($term) || is_wp_error($term)) {
                continue;
            }

            $tabTitle = empty($tabTitle) ? $term->name : $tabTitle;

            $this->tabs[$tabTitle] = array(
                'tab' => $term->term_id,
                'url' => get_term_link($term, $taxonomy),
                'type' => 'category'
            );
        }

        return $this->tabs = apply_filters(
            'jankx_ecommerce_category_tabs_products_tabs',
            $this->tabs
        );
    }

    public function buildFirstTabQuery()
    {
        if (!count($this->tabs)) {
            return;
        }
        $tabs = array_values($this->tabs);
        $firstTab = array_shift($tabs);
        if (is_array($firstTab)) {
            $firstTab = array_get($firstTab, 'tab', 'featured');
        }

        $firstTabQuery = GetProductQuery::buildQuery(wp_parse_args(
            array(
                'query_type' => $firstTab,
            ),
            $this->args,
        ));

        return $firstTabQuery->getWordPressQuery();
    }

    public function render()
    {
        TemplateManager::createProductJsTemplate();

        $tabs       = $this->generateTabs('category');
        $plugin = jankx_ecommerce()->getShopPlugin();
        $postLayoutManager = PostLayoutManager::getInstance(
            TemplateLoader::getTemplateEngine()
                ->getId()
        );

        do_action("jankx/ecommerce/loop/before", $this->args);


        $productLayout = $postLayoutManager->createLayout(
            array_get($this->args, 'layout', Card::LAYOUT_NAME),
            $this->buildFirstTabQuery()
        );
        $productLayout->setContentGenerator(
            $plugin->getContentGenerator()
        );

        // Render the output
        $content = EcommerceTemplate::render(
            'base/category/tabs-products',
            array(
                'tabs' => $tabs,
                'widget_title' => array_get($this->args, 'widget_title'),
                'first_tag' => array_get(array_values($tabs), 0),
                'readmore' => $this->readmore,
                'tab_content' => $productLayout->render(false),
                'plugin_name' => $plugin->getName(),
            ),
            null,
            false
        );

        return $content;
    }
}
