<?php

namespace Jankx\WooCommerce\Abstracts;

use Jankx\WooCommerce\Constracts\ProductDetailContentInterface;

abstract class ProductDetailContent implements ProductDetailContentInterface
{
    public function appendBodyClass()
    {
        add_filter('body_class', function ($bodyClasses) {
            $bodyClasses[] = sprintf('product-layout-%s', $this->getLayoutId());
            return $bodyClasses;
        });
    }

    public function openTopProductInfoWrap()
    {
        ?>
        <div class="jankx-top-product-infos">
        <?php
    }

    public function closeTopProductInfoWrap()
    {
        ?>
        </div>
        <?php
    }

    public function startLeftBlockTopInfo()
    {
        ?>
        <div class="jankx-block-features block-1">
        <?php
    }
    public function endLeftBlockTopInfo()
    {
        ?>
        </div> <!-- end left block -->
        <?php
    }

    public function startRightBlockTopInfo()
    {
        ?>
        <div class="jankx-block-features block-2">
        <?php
    }
    public function endRightBlockTopInfo()
    {
        ?>
        </div> <!-- end right block -->
        <?php
    }
}
