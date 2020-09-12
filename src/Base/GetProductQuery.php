<?php
namespace Jankx\Ecommerce\Base;

use WP_Query;
use WooCommerce;

class GetProductQuery extends QueryBuilder
{
    protected $type;
    protected $wordpressQuery;
    protected $limit = 10;

    /**
     * Set query type
     *
     * @param string $type the type of Query
     * @return \Jankx\Ecommerce\Base\GetProductQuery
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

    public function buildWordPressQuery()
    {
        $integrator = jankx_ecommerce()->getShopPlugin();
        $postType   = $integrator->getPostType();
        $taxQuery   = array();
        $queryArgs  = array(
            'post_type' => $postType,
            'posts_per_page' => apply_filters(
                "jankx_ecommerce_query_{$this->type}_limit",
                $this->limit
            ),
        );

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
        $queryArgs['tax_query'] = $taxQuery;

        return new WP_Query(apply_filters(
            'jankx_ecommerce_build_product_query',
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
}
