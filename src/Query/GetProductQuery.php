<?php
namespace Jankx\Ecommerce\Query;

use WP_Query;

class GetProductQuery
{
    protected $type;
    protected $wordpressQuery;
    protected $limit = 10;

    /**
     * Set query type
     *
     * @param string $type the type of Query
     * @return \Jankx\Ecommerce\Query\GetProductQuery
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

        $postType = jankx_ecommerce()->getShopPlugin()->getPostType();
        return new WP_Query(array(
            'post_type' => $postType,
            'posts_per_page' => $this->limit,
        ));
    }

    public function getWordPressQuery()
    {

        if (is_null($this->wordpressQuery)) {
            $this->wordpressQuery = $this->buildWordPressQuery();
        }

        return $this->wordpressQuery;
    }

    public static function buildQuery($args)
    {
        $query = new static();

        if (is_array($args)) {
            foreach ($args as $key => $val) {
                $method = preg_replace_callback(array('/^([a-z])/', '/[_|-]([a-z])/', '/.+/'), function ($matches) {
                    if (isset($matches[1])) {
                        return strtoupper($matches[1]);
                    }
                    return sprintf('set%s', $matches[0]);
                }, $key);

                if (method_exists($query, $method)) {
                    $query->$method($val);
                }
            }
        }

        return $query;
    }
}
