<?php

namespace Jankx\WooCommerce\Constracts;

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
