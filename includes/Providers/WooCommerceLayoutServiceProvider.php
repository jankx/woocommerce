<?php

namespace Jankx\WooCommerce\Providers;

use Jankx\Support\Providers\ServiceProvider;
use Jankx\WooCommerce\Helpers\Logger;
use Jankx\WooCommerce\LayoutSystem\LayoutManager;
use Jankx\WooCommerce\LayoutSystem\CssManager;
use Jankx\WooCommerce\LayoutSystem\SettingsManager;

/**
 * Class WooCommerceLayoutServiceProvider
 * 
 * Service Provider pattern để load config và khởi tạo layouts
 */
class WooCommerceLayoutServiceProvider extends ServiceProvider
{
    /**
     * @var WooCommerceLayoutServiceProvider Singleton instance
     */
    private static $instance = null;

    /**
     * @var array Config data
     */
    private $config = [];

    /**
     * @var string Config file path
     */
    private $configPath = '';

    /**
     * @var array Theme options
     */
    private $themeOptions = [];

    /**
     * @var LayoutManager
     */
    private $layoutManager;

    /**
     * @var SettingsManager
     */
    private $settingsManager;


    /**
     * Register service
     *
     * @param \Jankx\Foundation\Application $app
     * @return void
     */
    public function register($app)
    {
        $this->app = $app;
        $this->layoutManager = LayoutManager::getInstance();
        $this->settingsManager = SettingsManager::getInstance();
        
        // Set default config path
        $this->setConfigPath($this->getDefaultConfigPath());
    }

    /**
     * Bootstrap service
     *
     * @param \Jankx\Foundation\Application $app
     * @return void
     */
    public function boot($app)
    {
        $this->app = $app;
        
        // Load config và theme options
        add_action('after_setup_theme', [$this, 'loadConfiguration'], 15);
        
        // Apply configuration to layouts
        add_action('jankx_woocommerce_register_layouts', [$this, 'applyConfiguration'], 100);
        
        // Set default layouts từ config
        add_action('init', [$this, 'setDefaultLayouts'], 20);
    }

    /**
     * Set config file path
     *
     * @param string $path
     * @return self
     */
    public function setConfigPath(string $path): self
    {
        $this->configPath = $path;
        return $this;
    }

    /**
     * Get default config path
     *
     * @return string
     */
    private function getDefaultConfigPath(): string
    {
        // Try theme directory first
        $themePath = get_template_directory() . '/config/woocomerce.php';
        
        if (file_exists($themePath)) {
            return $themePath;
        }

        // Fallback to child theme
        if (is_child_theme()) {
            $childThemePath = get_stylesheet_directory() . '/config/woocomerce.php';
            if (file_exists($childThemePath)) {
                return $childThemePath;
            }
        }

        return '';
    }

    /**
     * Load configuration from file và theme options
     *
     * @return void
     */
    public function loadConfiguration(): void
    {
        \Jankx\WooCommerce\Helpers\Logger::info('ConfigProvider: Loading configuration', [
            'config_path' => $this->configPath,
        ]);

        // Load config file
        $this->loadConfigFile();

        // Load theme options
        $this->loadThemeOptions();

        // Merge config với priority: theme options > config file > defaults
        $this->config = $this->mergeConfiguration();
        
        \Jankx\WooCommerce\Helpers\Logger::info('ConfigProvider: Configuration loaded', [
            'layout_types' => count($this->config),
            'has_global' => isset($this->config['global']),
            'config_size' => strlen(json_encode($this->config)),
        ]);

        do_action('jankx_woocommerce_config_loaded', $this->config, $this);
    }

    /**
     * Load config from file
     *
     * @return void
     */
    private function loadConfigFile(): void
    {
        if (empty($this->configPath) || !file_exists($this->configPath)) {
            return;
        }

        $config = include $this->configPath;

        if (is_array($config)) {
            $this->config = $config;
        }
    }

    /**
     * Load theme options
     *
     * @return void
     */
    private function loadThemeOptions(): void
    {
        // Load từ WordPress options
        $optionKey = apply_filters('jankx_woocommerce_theme_option_key', 'jankx_woocommerce_options');
        $options = get_option($optionKey, []);

        if (is_array($options)) {
            $this->themeOptions = $options;
        }

        // Allow filter
        $this->themeOptions = apply_filters('jankx_woocommerce_theme_options', $this->themeOptions);
    }

    /**
     * Merge configuration với priority
     * Priority: Theme Options > Config File > Defaults
     *
     * @return array
     */
    private function mergeConfiguration(): array
    {
        $defaults = $this->getDefaultConfiguration();
        
        // Start with defaults
        $merged = $defaults;

        // Override with config file
        if (!empty($this->config)) {
            $merged = $this->deepMerge($merged, $this->config);
        }

        // Override with theme options (highest priority)
        if (!empty($this->themeOptions)) {
            $merged = $this->deepMerge($merged, $this->themeOptions);
        }

        return $merged;
    }

    /**
     * Deep merge arrays
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    private function deepMerge(array $array1, array $array2): array
    {
        $merged = $array1;

        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = $this->deepMerge($merged[$key], $value);
            } else {
                // Only override if value is not empty
                if (!empty($value) || $value === false || $value === 0) {
                    $merged[$key] = $value;
                }
            }
        }

        return $merged;
    }

    /**
     * Get default configuration structure
     *
     * @return array
     */
    private function getDefaultConfiguration(): array
    {
        return [
            // Product Detail Layouts
            'product_detail' => [
                'default_layout' => 'default-product-detail',
                'enabled' => true,
                'settings' => [
                    'gallery_width' => 50,
                    'show_related_products' => true,
                    'related_columns' => 4,
                    'sticky_add_to_cart' => true,
                ],
            ],

            // Product Loop Layouts
            'product_loop' => [
                'default_layout' => 'grid-product-loop',
                'enabled' => true,
                'settings' => [
                    'columns' => 4,
                    'columns_mobile' => 2,
                    'show_quick_view' => true,
                    'hover_effect' => 'zoom',
                    'show_badges' => true,
                ],
            ],

            // Product Category Block
            'product_category_block' => [
                'default_layout' => 'grid-category-block',
                'enabled' => true,
                'settings' => [
                    'columns' => 4,
                    'show_count' => true,
                    'show_description' => false,
                    'image_ratio' => '1/1',
                ],
            ],

            // Product Gallery
            'product_gallery' => [
                'default_layout' => 'slider-gallery',
                'enabled' => true,
                'settings' => [
                    'thumbnail_position' => 'bottom',
                    'thumbnail_size' => 100,
                    'enable_zoom' => true,
                    'enable_lightbox' => true,
                    'autoplay' => false,
                ],
            ],

            // Cart Form
            'cart_form' => [
                'default_layout' => 'default-cart-form',
                'enabled' => true,
                'settings' => [
                    'show_thumbnails' => true,
                    'thumbnail_size' => 60,
                    'editable' => true,
                ],
            ],

            // Cart Page
            'cart_page' => [
                'default_layout' => 'default-cart-page',
                'enabled' => true,
                'settings' => [
                    'show_cross_sells' => true,
                    'show_shipping_calculator' => true,
                    'table_style' => 'default',
                    'page_layout' => 'fullwidth',
                ],
            ],

            // Checkout Page
            'checkout_page' => [
                'default_layout' => 'default-checkout',
                'enabled' => true,
                'settings' => [
                    'layout_style' => 'two-column',
                    'multi_step' => false,
                    'steps_count' => 3,
                    'show_progress_bar' => true,
                    'field_spacing' => 15,
                ],
            ],

            // Quick Checkout
            'quick_checkout' => [
                'default_layout' => 'modal-quick-checkout',
                'enabled' => false,
                'settings' => [
                    'modal_width' => 600,
                    'enable_one_click' => true,
                    'show_progress' => true,
                    'trigger_type' => 'modal',
                ],
            ],

            // Global Settings
            'global' => [
                'primary_color' => '#0073aa',
                'secondary_color' => '#23282d',
                'border_radius' => 5,
                'spacing' => 20,
                'enable_cache' => true,
                'cache_expiration' => 3600,
            ],
        ];
    }

    /**
     * Apply configuration to layouts
     *
     * @param LayoutManager $manager
     * @return void
     */
    public function applyConfiguration(LayoutManager $manager): void
    {
        foreach ($this->config as $layoutType => $config) {
            if ($layoutType === 'global') {
                continue;
            }

            // Skip if disabled
            if (isset($config['enabled']) && !$config['enabled']) {
                continue;
            }

            // Apply settings to layouts of this type
            if (isset($config['settings']) && is_array($config['settings'])) {
                $layouts = $manager->getByType($layoutType);
                
                foreach ($layouts as $layout) {
                    $layoutId = $layout->getId();
                    
                    // Get existing settings
                    $existingSettings = $this->settingsManager->getLayoutSettings($layoutId);
                    
                    // Merge with config settings
                    $mergedSettings = array_merge($config['settings'], $existingSettings);
                    
                    // Save merged settings
                    $this->settingsManager->setLayoutSettings($layoutId, $mergedSettings);
                }
            }
        }

        do_action('jankx_woocommerce_configuration_applied', $this->config, $manager);
    }

    /**
     * Set default layouts từ config
     *
     * @return void
     */
    public function setDefaultLayouts(): void
    {
        foreach ($this->config as $layoutType => $config) {
            if ($layoutType === 'global') {
                continue;
            }

            if (isset($config['default_layout']) && !empty($config['default_layout'])) {
                $defaultLayoutId = $config['default_layout'];
                
                // Check if layout exists
                if ($this->layoutManager->has($defaultLayoutId)) {
                    $this->layoutManager->setDefault($layoutType, $defaultLayoutId);
                }
            }
        }
    }

    /**
     * Get configuration value
     *
     * @param string $key Dot notation key (e.g., 'product_detail.settings.gallery_width')
     * @param mixed $default Default value
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Get all configuration
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Get layout type configuration
     *
     * @param string $layoutType
     * @return array
     */
    public function getLayoutConfig(string $layoutType): array
    {
        return $this->config[$layoutType] ?? [];
    }

    /**
     * Check if layout type is enabled
     *
     * @param string $layoutType
     * @return bool
     */
    public function isLayoutEnabled(string $layoutType): bool
    {
        return $this->config[$layoutType]['enabled'] ?? true;
    }

    /**
     * Update theme option
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function updateThemeOption(string $key, $value): bool
    {
        $optionKey = apply_filters('jankx_woocommerce_theme_option_key', 'jankx_woocommerce_options');
        $options = get_option($optionKey, []);

        // Support dot notation
        $keys = explode('.', $key);
        $current = &$options;

        foreach ($keys as $i => $k) {
            if ($i === count($keys) - 1) {
                $current[$k] = $value;
            } else {
                if (!isset($current[$k]) || !is_array($current[$k])) {
                    $current[$k] = [];
                }
                $current = &$current[$k];
            }
        }

        return update_option($optionKey, $options);
    }

    /**
     * Reload configuration
     *
     * @return void
     */
    public function reload(): void
    {
        $this->config = [];
        $this->themeOptions = [];
        $this->loadConfiguration();
    }

}

