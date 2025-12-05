# Jankx Application Integration

## Tổng quan

WooCommerce Layout System có thể integrate hoàn toàn với Jankx Application Container, cho phép dependency injection và service management theo Laravel-style.

## Service Provider

### WooCommerceServiceProvider

Service Provider chính theo chuẩn Laravel/Jankx pattern:

```php
namespace Jankx\WooCommerce\Providers;

class WooCommerceServiceProvider extends ServiceProvider
{
    public function register() {
        // Register singletons vào container
    }
    
    public function boot() {
        // Bootstrap services
    }
}
```

### Registered Services

Các services được register vào container:

- `woocommerce.layout.manager` → `LayoutManager`
- `woocommerce.layout.css` → `CssManager`
- `woocommerce.layout.settings` → `SettingsManager`
- `woocommerce.layout.bootstrap` → `LayoutBootstrap`
- `woocommerce.layout.config` → `WooCommerceLayoutServiceProvider`
- `woocommerce.layout.theme_options` → `ThemeOptionsIntegration`

## Usage

### Auto Registration

Service Provider tự động register khi có Jankx Application:

```php
// Tự động qua hook
add_action('jankx_app_before_boot', function($app) {
    // Auto-registered
});
```

### Manual Registration (từ theme/plugin)

```php
use Jankx\WooCommerce\Helpers\ServiceProviderHelper;

// Register vào Jankx App
ServiceProviderHelper::registerToJankxApp();
```

### Get Services từ Container

**Method 1: Via Helper Functions**

```php
// Get Layout Manager
$layoutManager = jankx_woocommerce_layout_manager();

// Get CSS Manager
$cssManager = jankx_woocommerce_css_manager();

// Get Settings Manager
$settingsManager = jankx_woocommerce_settings_manager();

// Get Config Provider
$configProvider = jankx_woocommerce_config_provider();
```

**Method 2: Via Container**

```php
// Get từ container
$layoutManager = app('woocommerce.layout.manager');
$cssManager = app('woocommerce.layout.css');
$settingsManager = app('woocommerce.layout.settings');

// Hoặc via class name
$layoutManager = app(\Jankx\WooCommerce\LayoutSystem\LayoutManager::class);
```

**Method 3: Via Service Provider Helper**

```php
use Jankx\WooCommerce\Helpers\ServiceProviderHelper;

$layoutManager = ServiceProviderHelper::layoutManager();
$cssManager = ServiceProviderHelper::cssManager();
$settingsManager = ServiceProviderHelper::settingsManager();
```

**Method 4: Via WooCommerceServiceProvider Static Methods**

```php
use Jankx\WooCommerce\Providers\WooCommerceServiceProvider;

$layoutManager = WooCommerceServiceProvider::layoutManager();
$cssManager = WooCommerceServiceProvider::cssManager();
$settingsManager = WooCommerceServiceProvider::settingsManager();
```

## Dependency Injection

### Constructor Injection

```php
class MyCustomClass
{
    protected $layoutManager;
    
    public function __construct(LayoutManager $layoutManager)
    {
        $this->layoutManager = $layoutManager;
    }
}

// Resolve từ container
$myClass = app()->make(MyCustomClass::class);
```

### Method Injection

```php
class MyController
{
    public function index(LayoutManager $layoutManager)
    {
        $layouts = $layoutManager->all();
        // ...
    }
}
```

## Hooks và Integration Points

### Hooks Available

```php
// Khi package được registered
add_action('jankx_woocommerce_package_registered', function($app, $provider) {
    // Package registered
}, 10, 2);

// Khi package được booted
add_action('jankx_woocommerce_package_booted', function($app, $provider) {
    // Package booted
}, 10, 2);

// Khi layouts system ready
add_action('jankx_woocommerce_layouts_ready', function($app) {
    // Layouts system ready
    $layoutManager = app('woocommerce.layout.manager');
}, 10);

// After layouts ready
add_action('jankx_woocommerce_after_layouts_ready', function($app) {
    // Do something after ready
}, 10);
```

### Hook vào Service Provider Boot

```php
// Hook into service provider boot
add_action('jankx_woocommerce_service_provider_booted', function() {
    // Service provider đã boot
    $layoutManager = app('woocommerce.layout.manager');
});
```

## External Package Integration

### Từ Plugin khác

```php
// file: my-plugin/my-plugin.php

add_action('plugins_loaded', function() {
    // Check if Jankx WooCommerce available
    if (class_exists('\Jankx\WooCommerce\Helpers\ServiceProviderHelper')) {
        // Register custom layouts
        add_action('jankx_woocommerce_layouts_ready', function($app) {
            $layoutManager = app('woocommerce.layout.manager');
            $layoutManager->register(new MyPluginLayout());
        });
    }
});
```

### Từ Theme khác

```php
// file: functions.php

add_action('after_setup_theme', function() {
    // Check if WooCommerce Layouts ready
    if (function_exists('jankx_woocommerce_layouts_ready') && jankx_woocommerce_layouts_ready()) {
        $layoutManager = jankx_woocommerce_layout_manager();
        $layoutManager->register(new MyThemeLayout());
    }
});
```

## Advanced Usage

### Extend Service Provider

```php
namespace MyTheme\Providers;

use Jankx\WooCommerce\Providers\WooCommerceServiceProvider;

class MyWooCommerceServiceProvider extends WooCommerceServiceProvider
{
    public function register()
    {
        parent::register();
        
        // Add custom services
        $this->app->singleton('my.custom.service', function($app) {
            return new MyCustomService();
        });
    }
    
    public function boot()
    {
        parent::boot();
        
        // Additional boot logic
    }
}

// Register
add_action('jankx_app_before_boot', function($app) {
    $app->register(MyWooCommerceServiceProvider::class);
});
```

### Custom Service Registration

```php
// Register custom service vào existing provider
add_action('jankx_woocommerce_package_registered', function($app) {
    $app->singleton('my.woo.feature', function($app) {
        return new MyWooFeature(
            $app->make('woocommerce.layout.manager'),
            $app->make('woocommerce.layout.settings')
        );
    });
});
```

### Lazy Loading Services

```php
// Register lazy service
add_action('jankx_app_before_boot', function($app) {
    $app->registerLazy(MyLazyServiceProvider::class);
});

// Use lazy service
$service = app()->lazy('my.lazy.service');
```

## Testing với Container

### Unit Test Setup

```php
use PHPUnit\Framework\TestCase;
use Jankx\Foundation\Application;

class LayoutTest extends TestCase
{
    protected $app;
    
    protected function setUp(): void
    {
        $this->app = new Application();
        $this->app->register(WooCommerceServiceProvider::class);
    }
    
    public function testLayoutManager()
    {
        $layoutManager = $this->app->make('woocommerce.layout.manager');
        $this->assertInstanceOf(LayoutManager::class, $layoutManager);
    }
}
```

### Mock Services

```php
// Mock service trong test
$this->app->singleton('woocommerce.layout.manager', function() {
    return \Mockery::mock(LayoutManager::class);
});
```

## Benefits của Container Integration

### 1. Dependency Injection
✅ Automatic dependency resolution
✅ Constructor injection
✅ Method injection

### 2. Service Management
✅ Centralized service registry
✅ Singleton management
✅ Lazy loading support

### 3. Testing
✅ Easy mocking và stubbing
✅ Isolated testing
✅ Dependency injection trong tests

### 4. Extensibility
✅ Easy để extend và override
✅ Hook vào service lifecycle
✅ Custom service registration

### 5. Code Organization
✅ Clear separation of concerns
✅ Service-oriented architecture
✅ Modular design

## Migration từ Singleton sang Container

### Before (Singleton):

```php
$layoutManager = LayoutManager::getInstance();
```

### After (Container):

```php
$layoutManager = app('woocommerce.layout.manager');
// or
$layoutManager = jankx_woocommerce_layout_manager();
```

### Backward Compatibility

Cả hai cách đều hoạt động! Singleton pattern vẫn được giữ để backward compatibility:

```php
// Old way - still works
$layoutManager = LayoutManager::getInstance();

// New way - recommended
$layoutManager = app('woocommerce.layout.manager');
```

## Best Practices

### 1. Prefer Container Resolution

```php
// Good
$layoutManager = app('woocommerce.layout.manager');

// Acceptable
$layoutManager = jankx_woocommerce_layout_manager();

// Avoid (unless necessary)
$layoutManager = LayoutManager::getInstance();
```

### 2. Use Dependency Injection

```php
// Good
class MyClass {
    public function __construct(LayoutManager $manager) {
        $this->manager = $manager;
    }
}

// Avoid
class MyClass {
    public function __construct() {
        $this->manager = LayoutManager::getInstance();
    }
}
```

### 3. Register Services Early

```php
// Register trong hook phù hợp
add_action('jankx_app_before_boot', function($app) {
    // Register services here
});
```

### 4. Check Availability

```php
// Always check before use
if (jankx_woocommerce_layouts_ready()) {
    $layoutManager = jankx_woocommerce_layout_manager();
}
```

---

**Integration với Jankx Application mang lại architecture hiện đại và dễ maintain!** 🚀

