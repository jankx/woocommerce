<?php

namespace Jankx\WooCommerce\LayoutSystem;

use Jankx\WooCommerce\Helpers\Logger;
use Jankx\WooCommerce\Layouts\ProductDetail\DefaultProductDetailLayout;
use Jankx\WooCommerce\Layouts\ProductLoop\GridProductLoopLayout;
use Jankx\WooCommerce\Layouts\ProductLoop\ListProductLoopLayout;
use Jankx\WooCommerce\Layouts\CategoryBlock\GridCategoryBlockLayout;
use Jankx\WooCommerce\Layouts\Gallery\SliderGalleryLayout;
use Jankx\WooCommerce\Layouts\Cart\DefaultCartPageLayout;
use Jankx\WooCommerce\Layouts\Checkout\DefaultCheckoutLayout;
use Jankx\WooCommerce\Layouts\Checkout\MultiStepCheckoutLayout;
use Jankx\WooCommerce\Layouts\QuickCheckout\ModalQuickCheckoutLayout;

/**
 * Class LayoutBootstrap
 * 
 * Bootstrap và đăng ký tất cả default layouts
 */
class LayoutBootstrap
{
    /**
     * @var LayoutBootstrap Singleton instance
     */
    private static $instance = null;

    /**
     * @var \Jankx\WooCommerce\LayoutSystem\LayoutManager
     */
    private $layoutManager;

    /**
     * @var \Jankx\WooCommerce\LayoutSystem\CssManager
     */
    private $cssManager;

    /**
     * @var \Jankx\WooCommerce\LayoutSystem\SettingsManager
     */
    private $settingsManager;

    /**
     * Get singleton instance
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor
     */
    private function __construct()
    {
        \Jankx\WooCommerce\Helpers\Logger::info('LayoutBootstrap: Constructor called', [
            'class' => __CLASS__,
            'memory_mb' => round(memory_get_usage() / 1024 / 1024, 2),
        ]);

        $this->layoutManager = LayoutManager::getInstance();
        \Jankx\WooCommerce\Helpers\Logger::debug('LayoutBootstrap: LayoutManager initialized');

        $this->cssManager = CssManager::getInstance();
        \Jankx\WooCommerce\Helpers\Logger::debug('LayoutBootstrap: CssManager initialized');

        $this->settingsManager = SettingsManager::getInstance();
        \Jankx\WooCommerce\Helpers\Logger::debug('LayoutBootstrap: SettingsManager initialized');

        // Initialize Service Provider
        $this->initServiceProvider();

        $this->init();
        
        \Jankx\WooCommerce\Helpers\Logger::info('LayoutBootstrap: Initialization complete');
    }

    /**
     * Initialize Service Provider
     *
     * @return void
     */
    private function initServiceProvider(): void
    {
        \Jankx\WooCommerce\Helpers\Logger::debug('LayoutBootstrap: initServiceProvider called');
        
        // Register WooCommerce Service Provider vào Jankx Application
        add_filter('jankx.foundation.providers', function($providers) {
            $addedProviders = [];
            
            // Add main WooCommerce Service Provider
            if (class_exists('\Jankx\WooCommerce\Providers\WooCommerceServiceProvider')) {
                $providers[] = \Jankx\WooCommerce\Providers\WooCommerceServiceProvider::class;
                $addedProviders[] = 'WooCommerceServiceProvider';
            }
            
            // Add WooCommerce Layout Config Service Provider
            if (class_exists('\Jankx\WooCommerce\Providers\WooCommerceLayoutServiceProvider')) {
                $providers[] = \Jankx\WooCommerce\Providers\WooCommerceLayoutServiceProvider::class;
                $addedProviders[] = 'WooCommerceLayoutServiceProvider';
            }
            
            // Add Theme Options Integration Service Provider
            if (class_exists('\Jankx\WooCommerce\Providers\ThemeOptionsIntegration')) {
                $providers[] = \Jankx\WooCommerce\Providers\ThemeOptionsIntegration::class;
                $addedProviders[] = 'ThemeOptionsIntegration';
            }
            
            \Jankx\WooCommerce\Helpers\Logger::info('LayoutBootstrap: Service providers registered', [
                'providers' => $addedProviders,
                'total_providers' => count($providers),
            ]);
            
            return $providers;
        }, 10);
    }

    /**
     * Initialize bootstrap
     *
     * @return void
     */
    private function init(): void
    {
        // Register default layouts
        add_action('jankx_woocommerce_register_default_layouts', [$this, 'registerDefaultLayouts']);

        // Hook vào WooCommerce để inject CSS
        add_action('wp_enqueue_scripts', [$this, 'enqueueLayoutAssets'], 20);

        // Hook vào template để apply layouts
        add_filter('woocommerce_locate_template', [$this, 'locateLayoutTemplate'], 10, 3);
    }

    /**
     * Register all default layouts
     *
     * @param LayoutManager $manager
     * @return void
     */
    public function registerDefaultLayouts(LayoutManager $manager): void
    {
        // Product Detail Layouts
        $manager->register(new DefaultProductDetailLayout());

        // Product Loop Layouts
        $manager->register(new GridProductLoopLayout());
        $manager->register(new ListProductLoopLayout());

        // Category Block Layouts
        $manager->register(new GridCategoryBlockLayout());
        
        // Expand/Collapse Category Layout (Flatsome-style)
        $expandCollapseLayout = new \Jankx\WooCommerce\Layouts\CategoryBlock\ExpandCollapseCategoryLayout();
        $manager->register($expandCollapseLayout);
        
        Logger::info('LayoutBootstrap: Expand/Collapse Category Layout registered', [
            'layout_id' => $expandCollapseLayout->getId(),
            'layout_name' => $expandCollapseLayout->getName(),
        ]);

        // Gallery Layouts
        $manager->register(new SliderGalleryLayout());

        // Cart Layouts
        $manager->register(new DefaultCartPageLayout());

        // Checkout Layouts
        $manager->register(new DefaultCheckoutLayout());
        $manager->register(new MultiStepCheckoutLayout());

        // Quick Checkout Layouts
        $manager->register(new ModalQuickCheckoutLayout());

        do_action('jankx_woocommerce_layouts_registered', $manager);
    }

    /**
     * Enqueue layout assets
     *
     * @return void
     */
    public function enqueueLayoutAssets(): void
    {
        if (!is_woocommerce()) {
            return;
        }

        // Determine which layout to use based on current page
        $layout = $this->getCurrentLayout();

        if ($layout === null) {
            return;
        }

        // Get settings for this layout
        $settings = $this->settingsManager->getLayoutSettings($layout->getId());

        // Inject CSS
        $this->cssManager->injectLayoutCss($layout, $settings);
    }

    /**
     * Get current layout based on page context
     *
     * @return \Jankx\WooCommerce\Contracts\LayoutInterface|null
     */
    private function getCurrentLayout()
    {
        $layoutType = null;

        // Determine layout type based on current page
        if (is_product()) {
            $layoutType = 'product-detail';
        } elseif (is_shop() || is_product_category() || is_product_tag()) {
            $layoutType = 'product-loop';
        } elseif (is_cart()) {
            $layoutType = 'cart-page';
        } elseif (is_checkout()) {
            $layoutType = 'checkout-page';
        }

        if ($layoutType === null) {
            return null;
        }

        // Get default layout for this type
        $layout = $this->layoutManager->getDefault($layoutType);

        // Allow override via filter
        $layout = apply_filters('jankx_woocommerce_current_layout', $layout, $layoutType);

        return $layout;
    }

    /**
     * Locate layout template
     *
     * @param string $template
     * @param string $template_name
     * @param string $template_path
     * @return string
     */
    public function locateLayoutTemplate(string $template, string $template_name, string $template_path): string
    {
        // Allow themes to override layout templates
        $layout = $this->getCurrentLayout();

        if ($layout === null) {
            return $template;
        }

        $layoutId = $layout->getId();
        $themeTemplate = get_stylesheet_directory() . '/jankx-woocommerce/layouts/' . $layoutId . '/' . $template_name;

        if (file_exists($themeTemplate)) {
            return $themeTemplate;
        }

        return $template;
    }

    /**
     * Get layout manager
     *
     * @return LayoutManager
     */
    public function getLayoutManager(): LayoutManager
    {
        return $this->layoutManager;
    }

    /**
     * Get CSS manager
     *
     * @return CssManager
     */
    public function getCssManager(): CssManager
    {
        return $this->cssManager;
    }

    /**
     * Get settings manager
     *
     * @return SettingsManager
     */
    public function getSettingsManager(): SettingsManager
    {
        return $this->settingsManager;
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}

