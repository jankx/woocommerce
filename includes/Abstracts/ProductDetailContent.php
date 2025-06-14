<?php

namespace Jankx\WooCommerce\Abstracts;

if (!defined('ABSPATH')) {
    exit('Cheating huh?');
}

use Jankx\WooCommerce\Constracts\ProductDetailContentInterface;
use Jankx\WooCommerce\Constracts\ProductSummaryLayoutInterface;
use Jankx\WooCommerce\Layouts\ProductSummary\ProductVariationChooserAndInputSpinner;
use Jankx\WooCommerce\WooCommerce;

abstract class ProductDetailContent implements ProductDetailContentInterface
{
    /**
     * Summary of summaryLayout
     * @var \Jankx\WooCommerce\Constracts\ProductSummaryLayoutInterface
     */
    protected $summaryLayout = null;

    public function appendBodyClass()
    {
        add_filter('body_class', function ($bodyClasses) {
            $bodyClasses[] = sprintf('product-layout-%s', $this->getLayoutId());
            return $bodyClasses;
        });
    }

    public function openTopProductInfoWrap()
    {
        $this->loadProductSummaryLayout();
        ?>
        <div class="jankx-top-product-infos">
        <?php
    }

    public function closeTopProductInfoWrap()
    {
        ?>
        </div>
        <?php
        $this->resetProductSummaryLayout();
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

    public function initProductSummaryLayout()
    {
        $activeSummaryLayout = apply_filters(
            'jankx/woocommerce/product/summary/layout',
            null
        );
        if (empty($activeSummaryLayout)) {
            return;
        }

        $layouts = WooCommerce::instance()->getProductSummaryLayouts();
        $this->summaryLayout = isset($layouts[$activeSummaryLayout]) && is_a($layouts[$activeSummaryLayout], ProductSummaryLayoutInterface::class, true)
            ? new $layouts[$activeSummaryLayout]()
            : null;

        // Init summary layout
        if (!is_null($this->summaryLayout)) {
            $this->summaryLayout->init();
        }
    }

    public function loadProductSummaryLayout()
    {
        if (is_null($this->summaryLayout)) {
            return;
        }

        if ($this->summaryLayout->isVariantionChooser()) {
            add_filter(
                'woocommerce_dropdown_variation_attribute_options_html',
                [$this->summaryLayout, 'modifyVariationChooser'],
                10,
                2
            );
        }

        if ($this->summaryLayout->isUseSpinnerForQuantityInput()) {
            $this->summaryLayout->modifyQuantityInput();
        }
    }

    public function resetProductSummaryLayout()
    {
        if (is_null($this->summaryLayout)) {
            return;
        }

        if ($this->summaryLayout->isVariantionChooser()) {
            remove_filter(
                'woocommerce_dropdown_variation_attribute_options_html',
                [$this->summaryLayout, 'modifyVariationChooser'],
                10,
                2
            );
        }
    }
}
