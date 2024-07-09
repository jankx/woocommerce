<?php
namespace Jankx\WooCommerce\Base\Renderer;

use Jankx\TemplateAndLayout;
use Jankx\WooCommerce\WooCommerce;
use Jankx\WooCommerce\WooCommerceTemplate;
use Jankx\WooCommerce\Base\GetProductQuery;
use Jankx\WooCommerce\Base\TemplateManager;
use Jankx\PostLayout\PostLayoutManager;
use Jankx\PostLayout\Layout\Card;
use Jankx\PostLayout\Layout\Tabs;
use Jankx\PostLayout\Layout\Carousel;
use Jankx\Widget\Renderers\Base as RendererBase;

class CategoryTabsProductsRenderer extends RendererBase
{
    protected static $supportedFirstTabs;
    protected static $templateIsCreated;

    protected $categories = array();
    protected $readmore = array();
    protected $firstTab;
    protected $tabs;
    protected $options = array();
    protected $layoutOptions = array();

    public function __construct($categories, $firstTab = null)
    {
        $this->categories = $categories;
        $this->firstTab = $firstTab;

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

    public function generateTabs()
    {
        $this->tabs = [];
        if ($this->firstTab && isset(static::$supportedFirstTabs[$this->firstTab])) {
            $firstTabTitle = array_get(
                $this->options,
                'first_tab_title'
            );
            if (empty($firstTabTitle)) {
                $firstTabTitle = static::$supportedFirstTabs[$this->firstTab];
            }

            $this->tabs[$firstTabTitle] = array(
                'tab' => $this->firstTab,
                'url' => '#',
                'type' => 'special'
            );
        }
        $taxonomy = jankx_woocommerce()->getShopPlugin()->getProductCategoryTaxonomy();

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
            'jankx_woocommerce_category_tabs_products_tabs',
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
            $this->options,
        ));

        if (is_int($firstTab)) {
            $firstTabQuery->setCategories($firstTab);
        } else {
            $firstTabQuery->setCategories(array_map(function ($item) {
                return $item['tab'];
            }, $tabs));
        }
        return $firstTabQuery->getWordPressQuery();
    }

    public function transformDataTabs2PostLayoutTabs($tabs)
    {
        $shopPlugin = WooCommerce::instance()->getShopPlugin();
        $postLayoutTabs = array();

        foreach ($tabs as $tab_title => $tab) {
            if (!isset($tab['tab'])) {
                continue;
            }

            $postLayoutTabs[] = array(
                'title' => $tab_title,
                'object' => array(
                    'type' => 'taxonomy',
                    'type_name' => $shopPlugin->getProductCategoryTaxonomy(),
                    'id' => array_get($tab, 'tab'),
                ),
                'url' => array_get($tab, 'url'),
            );
        }
        return $postLayoutTabs;
    }

    public function render()
    {
        TemplateManager::createProductJsTemplate();

        $tabs = $this->generateTabs();
        $layout = array_get($this->options, 'sub_layout', Card::LAYOUT_NAME);
        /**
         * @var \Jankx\PostLayout\PostLayoutManager
         */
        $postLayoutManager = PostLayoutManager::getInstance(
            TemplateAndLayout::getTemplateEngine()
                ->getId()
        );

        if (is_null($postLayoutManager)) {
            return;
        }

        do_action("jankx/woocommerce/loop/before", $this->options);

        /**
         * @var \Jankx\PostLayout\Layout\Tabs
         */
        $productLayout = $postLayoutManager->createLayout(
            Tabs::LAYOUT_NAME,
            $this->buildFirstTabQuery()
        );
        $productLayout->setOptions(array_merge(
            [
                'wrap_tag_name' => 'ul',
            ],
            $this->layoutOptions
        ));

        $productLayout->setTabs($this->transformDataTabs2PostLayoutTabs($tabs));
        $productLayout->addChildLayout($layout);

        return $productLayout->render(false);
    }
}
