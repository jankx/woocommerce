<?php
namespace Jankx\Ecommerce\Constracts;

interface ShopPlugin
{
    public function getName();

    public function getCartUrl();

    public function getPostType();

    public function getProductCategoryTaxonomy();

    public function getProductMethod();

    public function getProductData($product);
}
