<?php
namespace Jankx\Ecommerce\Constracts;

interface ShopPlugin
{
    public function getCartUrl();

    public function getPostType();

    public function getProductCategoryTaxonomy();
}
