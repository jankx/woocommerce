<?php

namespace Jankx\WooCommerce\Constracts;

if (!defined('ABSPATH')) {
    exit('Cheating huh?');
}

interface ProductDetailContentInterface
{
    public function getLayoutId();

    public function appendBodyClass();

    public function openTopProductInfoWrap();
    public function closeTopProductInfoWrap();

    public function startLeftBlockTopInfo();
    public function endLeftBlockTopInfo();

    public function startRightBlockTopInfo();
    public function endRightBlockTopInfo();


    public function loadProductSummaryLayout();

    public function resetProductSummaryLayout();
}
