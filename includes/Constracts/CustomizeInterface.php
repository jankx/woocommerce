<?php

namespace Jankx\WooCommerce\Constracts;

interface CustomizeInterface
{
    public function getName();

    public function getCartUrl();

    public function getPostType();

    public function getProductCategoryTaxonomy();

    public function getProductMethod();

    public function getProductData($product);

    public function getCartContent($args = array());

    public function viewProduct();
}
