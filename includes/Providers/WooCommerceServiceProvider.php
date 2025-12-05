<?php

namespace Jankx\WooCommerce\Providers;

use Jankx\Support\Providers\ServiceProvider;
use Jankx\WooCommerce\LayoutSystem\LayoutManager;
use Jankx\WooCommerce\LayoutSystem\CssManager;
use Jankx\WooCommerce\LayoutSystem\SettingsManager;
use Jankx\WooCommerce\LayoutSystem\LayoutBootstrap;

/**
 * Class WooCommerceServiceProvider
 * 
 * Laravel-style Service Provider cho WooCommerce Layout System
 * Integrate với Jankx Application Container
 */
class WooCommerceServiceProvider extends ServiceProvider
{
    /**
     * All of the container singletons that should be registered.
     *
     * @var array
     */
    public $singletons = [
        'woocommerce.layout.manager' => LayoutManager::class,
        'woocommerce.layout.css' => CssManager::class,
        'woocommerce.layout.settings' => SettingsManager::class,
        'woocommerce.layout.bootstrap' => LayoutBootstrap::class,
    ];

    /**
     * Register any application services.
     *
     * @param \Jankx\Foundation\Application $app
     * @return void
     */
    public function register($app)
    {
        \Jankx\WooCommerce\Helpers\Logger::info('WooCommerceServiceProvider: register() called', [
            'provider' => __CLASS__,
        ]);

        // Register singletons
        $this->app->singleton('woocommerce.layout.manager', function ($app) {
            \Jankx\WooCommerce\Helpers\Logger::debug('ServiceProvider: Binding woocommerce.layout.manager');
            return LayoutManager::getInstance();
        });

        $this->app->singleton('woocommerce.layout.css', function ($app) {
            return CssManager::getInstance();
        });

        $this->app->singleton('woocommerce.layout.settings', function ($app) {
            return SettingsManager::getInstance();
        });

        $this->app->singleton('woocommerce.layout.bootstrap', function ($app) {
            return LayoutBootstrap::getInstance();
        });

        // Note: WooCommerceLayoutServiceProvider và ThemeOptionsIntegration
        // sẽ được register riêng trong LayoutBootstrap

        // Register aliases
        $this->registerAliases();

        // Register facades
        $this->registerFacades();
    }

    /**
     * Bootstrap any application services.
     *
     * @param \Jankx\Foundation\Application $app
     * @return void
     */
    public function boot($app)
    {
        \Jankx\WooCommerce\Helpers\Logger::info('WooCommerceServiceProvider: boot() called', [
            'provider' => __CLASS__,
        ]);

        // Initialize Layout Bootstrap
        // Bootstrap sẽ tự động register các providers khác
        $bootstrap = $this->app->make('woocommerce.layout.bootstrap');
        
        \Jankx\WooCommerce\Helpers\Logger::info('WooCommerceServiceProvider: Services booted successfully', [
            'bootstrap' => get_class($bootstrap),
        ]);

        // Register hooks
        $this->registerHooks();

        // Publish configs và assets nếu cần
        $this->publishResources();
    }

    /**
     * Register service aliases
     *
     * @return void
     */
    protected function registerAliases(): void
    {
        $this->app->alias('woocommerce.layout.manager', LayoutManager::class);
        $this->app->alias('woocommerce.layout.css', CssManager::class);
        $this->app->alias('woocommerce.layout.settings', SettingsManager::class);
        $this->app->alias('woocommerce.layout.bootstrap', LayoutBootstrap::class);
        $this->app->alias('woocommerce.layout.config', WooCommerceLayoutServiceProvider::class);
    }

    /**
     * Register facades
     *
     * @return void
     */
    protected function registerFacades(): void
    {
        // Facades sẽ được register nếu cần
        // $this->app->singleton('WooLayout', function ($app) {
        //     return new WooLayoutFacade($app);
        // });
    }

    /**
     * Register WordPress hooks
     *
     * @return void
     */
    protected function registerHooks(): void
    {
        // Hook để cho phép external registration
        add_action('jankx_woocommerce_service_provider_booted', function () {
            do_action('jankx_woocommerce_layouts_ready', $this->app);
        });
    }

    /**
     * Publish resources (config, assets, etc.)
     *
     * @return void
     */
    protected function publishResources(): void
    {
        // Nếu dùng artisan-style publishing (không cần trong WordPress)
        // Nhưng có thể dùng để copy default configs
        
        // Check và create config file nếu chưa có
        $configPath = get_template_directory() . '/config/woocomerce.php';
        
        if (!file_exists($configPath)) {
            $this->createDefaultConfig($configPath);
        }
    }

    /**
     * Create default config file
     *
     * @param string $path
     * @return void
     */
    protected function createDefaultConfig(string $path): void
    {
        // Config file sẽ được tạo manually hoặc copy từ example
        // Không auto-generate vì WooCommerceLayoutServiceProvider không còn singleton
        
        // User có thể copy từ: config/woocomerce.php.example
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'woocommerce.layout.manager',
            'woocommerce.layout.css',
            'woocommerce.layout.settings',
            'woocommerce.layout.bootstrap',
            'woocommerce.layout.config',
            'woocommerce.layout.theme_options',
        ];
    }

    /**
     * Get service from container (helper method)
     *
     * @param string $service
     * @return mixed
     */
    public static function getService(string $service)
    {
        if (!app()->bound($service)) {
            throw new \Exception("Service '{$service}' not found in container");
        }

        return app($service);
    }

    /**
     * Get Layout Manager instance
     *
     * @return LayoutManager
     */
    public static function layoutManager(): LayoutManager
    {
        return self::getService('woocommerce.layout.manager');
    }

    /**
     * Get CSS Manager instance
     *
     * @return CssManager
     */
    public static function cssManager(): CssManager
    {
        return self::getService('woocommerce.layout.css');
    }

    /**
     * Get Settings Manager instance
     *
     * @return SettingsManager
     */
    public static function settingsManager(): SettingsManager
    {
        return self::getService('woocommerce.layout.settings');
    }

    /**
     * Get Config Provider instance
     *
     * @return WooCommerceLayoutServiceProvider
     */
    public static function configProvider(): WooCommerceLayoutServiceProvider
    {
        return self::getService('woocommerce.layout.config');
    }
}

