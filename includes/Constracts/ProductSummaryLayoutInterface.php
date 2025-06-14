<?php

namespace Jankx\WooCommerce\Constracts;

if (!defined('ABSPATH')) {
    exit('Cheating huh?');
}

interface ProductSummaryLayoutInterface
{
    public function showCallNowButton();

    public function isVariantionChooser();
    public function isUseSpinnerForQuantityInput();


    public function modifyVariationChooser($html, $args);

    public function modifyQuantityInput();
}
