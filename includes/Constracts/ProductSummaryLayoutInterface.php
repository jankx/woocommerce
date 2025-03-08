<?php

namespace Jankx\WooCommerce\Constracts;

interface ProductSummaryLayoutInterface
{
    public function showCallNowButton();

    public function isVariantionChooser();
    public function isUseSpinnerForQuantityInput();


    public function modifyVariationChooser($html, $args);

    public function modifyQuantityInput();
}
