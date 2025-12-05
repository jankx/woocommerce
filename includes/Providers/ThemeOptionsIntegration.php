<?php

namespace Jankx\WooCommerce\Providers;

use Jankx\Support\Providers\ServiceProvider;

/**
 * Class ThemeOptionsIntegration
 * 
 * Integration với các theme options framework (Redux, Titan, etc.)
 */
class ThemeOptionsIntegration extends ServiceProvider
{
    /**
     * @var ThemeOptionsIntegration Singleton instance
     */
    private static $instance = null;

    /**
     * @var WooCommerceLayoutServiceProvider
     */
    private $configProvider;

    /**
     * Register service
     *
     * @param \Jankx\Foundation\Application $app
     * @return void
     */
    public function register($app)
    {
        $this->app = $app;
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
        
        // Get config provider từ container
        if ($app->bound('woocommerce.layout.config')) {
            $this->configProvider = $app->make('woocommerce.layout.config');
        }
        
        // Hook để get theme options từ các frameworks
        add_filter('jankx_woocommerce_theme_options', [$this, 'loadFromThemeFramework'], 10);
        
        // Hook để sync options khi save
        add_action('update_option', [$this, 'syncOnOptionUpdate'], 10, 3);
    }

    /**
     * Load options từ theme framework
     *
     * @param array $options Current options
     * @return array
     */
    public function loadFromThemeFramework(array $options): array
    {
        // Redux Framework
        if ($this->hasReduxFramework()) {
            $reduxOptions = $this->getReduxOptions();
            $options = array_merge($options, $reduxOptions);
        }

        // Titan Framework
        if ($this->hasTitanFramework()) {
            $titanOptions = $this->getTitanOptions();
            $options = array_merge($options, $titanOptions);
        }

        // Kirki
        if ($this->hasKirki()) {
            $kirkiOptions = $this->getKirkiOptions();
            $options = array_merge($options, $kirkiOptions);
        }

        // Custom theme options (get_theme_mod)
        $customOptions = $this->getCustomThemeMods();
        $options = array_merge($options, $customOptions);

        return $options;
    }

    /**
     * Check if Redux Framework is active
     *
     * @return bool
     */
    private function hasReduxFramework(): bool
    {
        return class_exists('ReduxFramework');
    }

    /**
     * Get options from Redux Framework
     *
     * @return array
     */
    private function getReduxOptions(): array
    {
        $options = [];
        
        // Get Redux option name
        $optName = apply_filters('jankx_woocommerce_redux_opt_name', 'cheephub_options');
        
        if (function_exists('redux_get_option')) {
            // Product Detail
            $options['product_detail']['settings']['gallery_width'] = 
                redux_get_option($optName, 'product_detail_gallery_width');
            $options['product_detail']['settings']['show_related_products'] = 
                redux_get_option($optName, 'product_detail_show_related');
            $options['product_detail']['settings']['sticky_add_to_cart'] = 
                redux_get_option($optName, 'product_detail_sticky_cart');

            // Product Loop
            $options['product_loop']['settings']['columns'] = 
                redux_get_option($optName, 'product_loop_columns');
            $options['product_loop']['settings']['hover_effect'] = 
                redux_get_option($optName, 'product_loop_hover_effect');
            $options['product_loop']['settings']['show_quick_view'] = 
                redux_get_option($optName, 'product_loop_quick_view');

            // Cart Page
            $options['cart_page']['settings']['show_cross_sells'] = 
                redux_get_option($optName, 'cart_show_cross_sells');

            // Checkout Page
            $options['checkout_page']['settings']['layout_style'] = 
                redux_get_option($optName, 'checkout_layout_style');
            $options['checkout_page']['settings']['multi_step'] = 
                redux_get_option($optName, 'checkout_multi_step');

            // Global
            $options['global']['primary_color'] = 
                redux_get_option($optName, 'primary_color');
            $options['global']['border_radius'] = 
                redux_get_option($optName, 'border_radius');
        }

        return array_filter($options);
    }

    /**
     * Check if Titan Framework is active
     *
     * @return bool
     */
    private function hasTitanFramework(): bool
    {
        return class_exists('TitanFramework');
    }

    /**
     * Get options from Titan Framework
     *
     * @return array
     */
    private function getTitanOptions(): array
    {
        $options = [];
        
        if (class_exists('TitanFramework')) {
            $titan = \TitanFramework::getInstance('cheephub');
            
            if ($titan) {
                // Product Detail
                $options['product_detail']['settings']['gallery_width'] = 
                    $titan->getOption('product_detail_gallery_width');
                
                // Product Loop
                $options['product_loop']['settings']['columns'] = 
                    $titan->getOption('product_loop_columns');
                
                // Global
                $options['global']['primary_color'] = 
                    $titan->getOption('primary_color');
            }
        }

        return array_filter($options);
    }

    /**
     * Check if Kirki is active
     *
     * @return bool
     */
    private function hasKirki(): bool
    {
        return class_exists('Kirki');
    }

    /**
     * Get options from Kirki
     *
     * @return array
     */
    private function getKirkiOptions(): array
    {
        $options = [];
        
        if (function_exists('get_theme_mod')) {
            // Kirki stores in theme mods
            $options['product_detail']['settings']['gallery_width'] = 
                get_theme_mod('product_detail_gallery_width');
            $options['product_loop']['settings']['columns'] = 
                get_theme_mod('product_loop_columns');
            $options['global']['primary_color'] = 
                get_theme_mod('primary_color');
        }

        return array_filter($options);
    }

    /**
     * Get custom theme mods
     *
     * @return array
     */
    private function getCustomThemeMods(): array
    {
        $options = [];

        // Map theme mods to config structure
        $mappings = [
            // Product Detail
            'woo_product_gallery_width' => 'product_detail.settings.gallery_width',
            'woo_show_related' => 'product_detail.settings.show_related_products',
            'woo_sticky_cart' => 'product_detail.settings.sticky_add_to_cart',
            
            // Product Loop
            'woo_loop_columns' => 'product_loop.settings.columns',
            'woo_loop_hover' => 'product_loop.settings.hover_effect',
            'woo_quick_view' => 'product_loop.settings.show_quick_view',
            
            // Cart
            'woo_cart_cross_sells' => 'cart_page.settings.show_cross_sells',
            
            // Checkout
            'woo_checkout_layout' => 'checkout_page.settings.layout_style',
            'woo_checkout_multistep' => 'checkout_page.settings.multi_step',
            
            // Global
            'woo_primary_color' => 'global.primary_color',
            'woo_border_radius' => 'global.border_radius',
        ];

        foreach ($mappings as $themeMod => $configPath) {
            $value = get_theme_mod($themeMod);
            
            if ($value !== false && $value !== null) {
                $this->setNestedValue($options, $configPath, $value);
            }
        }

        return $options;
    }

    /**
     * Set nested value using dot notation
     *
     * @param array $array
     * @param string $path
     * @param mixed $value
     * @return void
     */
    private function setNestedValue(array &$array, string $path, $value): void
    {
        $keys = explode('.', $path);
        $current = &$array;

        foreach ($keys as $i => $key) {
            if ($i === count($keys) - 1) {
                $current[$key] = $value;
            } else {
                if (!isset($current[$key]) || !is_array($current[$key])) {
                    $current[$key] = [];
                }
                $current = &$current[$key];
            }
        }
    }

    /**
     * Sync options khi update
     *
     * @param string $option
     * @param mixed $old_value
     * @param mixed $value
     * @return void
     */
    public function syncOnOptionUpdate(string $option, $old_value, $value): void
    {
        // Check if option liên quan đến WooCommerce layouts
        $relatedOptions = [
            'cheephub_options', // Redux
            'jankx_woocommerce_options',
        ];

        if (in_array($option, $relatedOptions, true)) {
            // Reload configuration
            if ($this->configProvider && method_exists($this->configProvider, 'reload')) {
                $this->configProvider->reload();
            }
            
            // Clear CSS cache
            $this->clearLayoutsCache();
        }
    }

    /**
     * Clear layouts CSS cache
     *
     * @return void
     */
    private function clearLayoutsCache(): void
    {
        $cssManager = \Jankx\WooCommerce\LayoutSystem\CssManager::getInstance();
        $layoutManager = \Jankx\WooCommerce\LayoutSystem\LayoutManager::getInstance();

        foreach ($layoutManager->all() as $layout) {
            $cssManager->clearCache($layout->getId());
        }
    }

}

