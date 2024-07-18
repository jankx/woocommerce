<?php

namespace Jankx\WooCommerce;

abstract class QueryBuilder
{
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
