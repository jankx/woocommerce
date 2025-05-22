<?php

namespace Jankx\WooCommerce;

use WP_Query;
use WooCommerce;

class GetProductQuery extends QueryBuilder
{
    protected $type;
    protected $categories = array();
    protected $wordpressQuery;
    protected $limit = 10;
    protected $fields = '';

    protected $paged = 1;

    /**
     * Set query type
     *
     * @param string $type the type of Query
     * @return \Jankx\WooCommerce\GetProductQuery
     */
    public function setQueryType($type = 'recents')
    {
        $this->type = $type;

        return $this;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setCategories($categoryIds)
    {
        if (is_int($categoryIds)) {
            $this->categories = array($categoryIds);
        } else {
             $this->categories = (array) $categoryIds;
        }
    }

    public function buildWordPressQuery()
    {
        $integrator = jankx_woocommerce()->getShopPlugin();
        $postType   = $integrator->getPostType();
        $taxQuery   = array();
        $queryArgs  = array(
            'post_type' => $postType,
            'fields' => $this->fields,
            'posts_per_page' => apply_filters(
                "jankx_woocommerce_query_{$this->type}_limit",
                $this->limit
            ),
            'paged' => $this->paged
        );

        if (!empty($this->categories)) {
            $taxQuery[] = array(
                'taxonomy' => $integrator->getProductCategoryTaxonomy(),
                'field'    => 'ids',
                'terms'    => $this->categories,
                'operator' => 'IN',
            );
        }

        if ($this->type === 'featured') {
            if ($integrator->getName() === 'woocommerce') {
                if (version_compare(WC()->version, '3.0.0') >= 0) {
                    $taxQuery[] = array(
                        'taxonomy' => 'product_visibility',
                        'field'    => 'name',
                        'terms'    => 'featured',
                        'operator' => 'IN',
                    );
                } else {
                    $queryArgs['meta_key']   = '_featured';
                    $queryArgs['meta_value'] = 'yes';
                }
            }
        }

        if ($this->type === 'sales') {
            $queryArgs['post__in'] = wc_get_product_ids_on_sale();
        }
        if ($this->type === 'search_query') {
            $queryKeyword = apply_filters('jankx/woocommerce/query/search/name', 's');
            if (isset($_GET[$queryKeyword]) && !empty($_GET[$queryKeyword])) {
                $queryArgs['s'] = $_GET[$queryKeyword];
            } elseif (!empty($_GET['s'])) {
                $queryArgs['s'] = $_GET['s'];
            }
        }
        $queryArgs['tax_query'] = $taxQuery;

        return new WP_Query(apply_filters(
            'jankx_woocommerce_build_product_query',
            $queryArgs
        ));
    }

    public function getWordPressQuery()
    {

        if (is_null($this->wordpressQuery)) {
            $this->wordpressQuery = $this->buildWordPressQuery();
        }

        return $this->wordpressQuery;
    }

    public function setPaged($paged)
    {
        $this->paged = intval($paged);
    }
}
