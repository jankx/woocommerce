# WooCommerce Layout Service Provider

## Tổng quan

Service Provider pattern cho phép load configuration từ file và theme options một cách tự động và có priority rõ ràng.

## Priority của Configuration

Configuration được merge theo thứ tự ưu tiên:

```
1. Theme Options (Highest Priority)
   ↓
2. Config File
   ↓
3. Default Configuration (Lowest Priority)
```

Nếu một value không được set trong Theme Options, hệ thống sẽ fallback sang Config File, sau đó mới đến Default.

## Cấu trúc Config File

File config: `themes/cheephub/config/woocomerce.php`

```php
<?php
return [
    'product_detail' => [
        'default_layout' => 'default-product-detail',
        'enabled' => true,
        'settings' => [
            'gallery_width' => 50,
            'show_related_products' => true,
            // ... more settings
        ],
    ],
    
    'product_loop' => [
        'default_layout' => 'grid-product-loop',
        'enabled' => true,
        'settings' => [
            'columns' => 4,
            'hover_effect' => 'zoom',
            // ... more settings
        ],
    ],
    
    // ... other layout types
    
    'global' => [
        'primary_color' => '#0073aa',
        'border_radius' => 5,
        // ... global settings
    ],
];
```

## Theme Options Integration

### Hỗ trợ các Framework

Service Provider tự động integrate với:
- **Redux Framework**
- **Titan Framework**
- **Kirki**
- **WordPress Theme Mods** (get_theme_mod)

### Redux Framework Example

```php
// Redux options sẽ tự động được load
// Option name: 'cheephub_options' (có thể filter)

// Các field tương ứng:
$options['product-detail']['gallery-width'] // → product_detail_gallery_width
$options['product-detail']['show-related']  // → product_detail_show_related
$options['product-loop']['columns']         // → product_loop_columns
```

### Custom Theme Mods Mapping

```php
// Theme mods được map tự động:
get_theme_mod('woo_product_gallery_width')  // → product_detail.settings.gallery_width
get_theme_mod('woo_loop_columns')           // → product_loop.settings.columns
get_theme_mod('woo_primary_color')          // → global.primary_color
```

## Service Provider API

### Get Instance

```php
$provider = \Jankx\WooCommerce\Providers\WooCommerceLayoutServiceProvider::getInstance();
```

### Get Configuration

```php
// Get single value với dot notation
$galleryWidth = $provider->get('product_detail.settings.gallery_width');
$primaryColor = $provider->get('global.primary_color', '#0073aa');

// Get full config
$allConfig = $provider->getConfig();

// Get layout type config
$productDetailConfig = $provider->getLayoutConfig('product_detail');
```

### Check Layout Status

```php
// Check if layout type is enabled
if ($provider->isLayoutEnabled('quick_checkout')) {
    // Do something
}
```

### Update Theme Option

```php
// Update theme option programmatically
$provider->updateThemeOption('product_loop.settings.columns', 3);

// Reload configuration
$provider->reload();
```

## Workflow

### 1. Initialization Flow

```
LayoutBootstrap::__construct()
    ↓
initServiceProvider()
    ↓
WooCommerceLayoutServiceProvider::getInstance()
    ↓
ThemeOptionsIntegration::getInstance()
    ↓
Load config file + theme options
    ↓
Merge configurations
    ↓
Apply to layouts
```

### 2. Configuration Loading Flow

```
loadConfiguration()
    ↓
loadConfigFile()           (from config/woocomerce.php)
    ↓
loadThemeOptions()         (from Redux/Titan/Kirki/Theme Mods)
    ↓
mergeConfiguration()       (Priority: Theme Options > Config > Defaults)
    ↓
Hook: jankx_woocommerce_config_loaded
```

### 3. Apply Configuration Flow

```
Hook: jankx_woocommerce_register_layouts (priority 100)
    ↓
applyConfiguration()
    ↓
Loop through layout types
    ↓
Get layouts of type
    ↓
Merge settings
    ↓
SettingsManager::setLayoutSettings()
    ↓
Hook: jankx_woocommerce_configuration_applied
```

## Usage Examples

### Example 1: Basic Config File

```php
// themes/cheephub/config/woocomerce.php
<?php
return [
    'product_detail' => [
        'default_layout' => 'my-custom-layout',
        'settings' => [
            'gallery_width' => 60,
            'sticky_add_to_cart' => true,
        ],
    ],
];
```

### Example 2: Redux Framework Integration

```php
// Redux config
Redux::setSection('cheephub_options', [
    'id' => 'woocommerce-layouts',
    'title' => __('WooCommerce Layouts', 'cheephub'),
    'fields' => [
        [
            'id' => 'product_detail_gallery_width',
            'type' => 'slider',
            'title' => __('Gallery Width', 'cheephub'),
            'default' => 50,
            'min' => 30,
            'max' => 70,
        ],
        [
            'id' => 'product_loop_columns',
            'type' => 'select',
            'title' => __('Product Columns', 'cheephub'),
            'options' => [
                2 => '2 Columns',
                3 => '3 Columns',
                4 => '4 Columns',
                5 => '5 Columns',
            ],
            'default' => 4,
        ],
    ],
]);
```

### Example 3: Custom Theme Mods

```php
// Customizer
$wp_customize->add_setting('woo_loop_columns', [
    'default' => 4,
    'sanitize_callback' => 'absint',
]);

$wp_customize->add_control('woo_loop_columns', [
    'label' => __('Product Loop Columns', 'cheephub'),
    'section' => 'woocommerce',
    'type' => 'number',
]);

// Tự động được load và apply
```

### Example 4: Programmatic Update

```php
// Update settings programmatically
add_action('init', function() {
    $provider = \Jankx\WooCommerce\Providers\WooCommerceLayoutServiceProvider::getInstance();
    
    // Update if condition met
    if (user_is_premium()) {
        $provider->updateThemeOption('quick_checkout.enabled', true);
        $provider->reload();
    }
});
```

### Example 5: Hook vào Configuration

```php
// Modify configuration sau khi load
add_filter('jankx_woocommerce_config_loaded', function($config, $provider) {
    // Override for specific condition
    if (is_mobile()) {
        $config['product_loop']['settings']['columns_mobile'] = 1;
    }
    
    return $config;
}, 10, 2);

// After configuration applied
add_action('jankx_woocommerce_configuration_applied', function($config, $manager) {
    // Log hoặc do something
    error_log('Layouts configured: ' . count($manager->all()));
}, 10, 2);
```

## Theme Options Frameworks Mapping

### Redux Framework

```php
// Map Redux options to config
[
    'product_detail_gallery_width'    => 'product_detail.settings.gallery_width',
    'product_detail_show_related'     => 'product_detail.settings.show_related_products',
    'product_detail_sticky_cart'      => 'product_detail.settings.sticky_add_to_cart',
    'product_loop_columns'            => 'product_loop.settings.columns',
    'product_loop_hover_effect'       => 'product_loop.settings.hover_effect',
    'product_loop_quick_view'         => 'product_loop.settings.show_quick_view',
    'cart_show_cross_sells'           => 'cart_page.settings.show_cross_sells',
    'checkout_layout_style'           => 'checkout_page.settings.layout_style',
    'checkout_multi_step'             => 'checkout_page.settings.multi_step',
    'primary_color'                   => 'global.primary_color',
    'border_radius'                   => 'global.border_radius',
]
```

### Custom Theme Mods

```php
// Map theme mods to config
[
    'woo_product_gallery_width'   => 'product_detail.settings.gallery_width',
    'woo_show_related'            => 'product_detail.settings.show_related_products',
    'woo_sticky_cart'             => 'product_detail.settings.sticky_add_to_cart',
    'woo_loop_columns'            => 'product_loop.settings.columns',
    'woo_loop_hover'              => 'product_loop.settings.hover_effect',
    'woo_quick_view'              => 'product_loop.settings.show_quick_view',
    'woo_cart_cross_sells'        => 'cart_page.settings.show_cross_sells',
    'woo_checkout_layout'         => 'checkout_page.settings.layout_style',
    'woo_checkout_multistep'      => 'checkout_page.settings.multi_step',
    'woo_primary_color'           => 'global.primary_color',
    'woo_border_radius'           => 'global.border_radius',
]
```

## Best Practices

### 1. Config File Structure

- ✅ Keep config file organized by layout type
- ✅ Use descriptive keys
- ✅ Provide sensible defaults
- ✅ Document each section
- ✅ Use null for "not set" values

### 2. Theme Options Integration

- ✅ Use consistent naming convention
- ✅ Prefix theme mods to avoid conflicts
- ✅ Provide validation và sanitization
- ✅ Use appropriate field types
- ✅ Group related options

### 3. Performance

- ✅ Configuration cached automatically
- ✅ Only reload when options change
- ✅ Clear CSS cache on config update
- ✅ Use transients for heavy operations

### 4. Extensibility

- ✅ Use hooks to modify configuration
- ✅ Allow custom config paths
- ✅ Support custom theme frameworks
- ✅ Provide clear APIs

## Troubleshooting

### Config không load

```php
// Debug config loading
add_action('jankx_woocommerce_config_loaded', function($config) {
    error_log('Loaded config: ' . print_r($config, true));
});
```

### Theme options không apply

```php
// Check theme options integration
$integration = \Jankx\WooCommerce\Providers\ThemeOptionsIntegration::getInstance();
$options = apply_filters('jankx_woocommerce_theme_options', []);
var_dump($options);
```

### Priority issues

```php
// Force reload configuration
$provider = \Jankx\WooCommerce\Providers\WooCommerceLayoutServiceProvider::getInstance();
$provider->reload();
```

---

**Service Provider giúp quản lý configuration một cách centralized, flexible và maintainable!** 🚀

