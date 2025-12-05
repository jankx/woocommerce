# Quick Start Guide - Jankx WooCommerce Layout System

## 5 Phút Setup

### Bước 1: Kiểm tra Bootstrap

File `bootstrap.php` đã tự động load Layout System. Không cần làm gì thêm.

### Bước 2: Tạo Config File (Optional)

Tạo file: `themes/your-theme/config/woocomerce.php`

```php
<?php
return [
    'product_loop' => [
        'settings' => [
            'columns' => 4,
            'hover_effect' => 'zoom',
        ],
    ],
];
```

### Bước 3: Xong!

Layouts đã hoạt động với default settings. Bạn có thể customize qua:
- Config file (như trên)
- Theme Options (Redux/Titan/Customizer)
- Code (hooks và filters)

## Common Tasks

### Task 1: Thay đổi số cột product loop

**Via Config File:**
```php
'product_loop' => [
    'settings' => [
        'columns' => 3,
    ],
],
```

**Via Theme Customizer:**
```php
// functions.php
$wp_customize->add_setting('woo_loop_columns', [
    'default' => 4,
]);
```

**Via Code:**
```php
add_filter('jankx_woocommerce_current_layout', function($layout, $type) {
    if ($type === 'product-loop') {
        $settings = SettingsManager::getInstance();
        $settings->set('grid-product-loop.columns', 3);
    }
    return $layout;
}, 10, 2);
```

### Task 2: Tạo custom layout

```php
// file: includes/MyCustomLayout.php
class MyCustomLayout extends AbstractProductDetailLayout
{
    public function __construct() {
        parent::__construct('my-layout', 'My Layout', 'product-detail');
    }
}

// Register
add_action('jankx_woocommerce_register_layouts', function($manager) {
    $manager->register(new MyCustomLayout());
});

// Set as default
add_filter('jankx_woocommerce_current_layout', function($layout, $type) {
    if ($type === 'product-detail') {
        return LayoutManager::getInstance()->get('my-layout');
    }
    return $layout;
}, 10, 2);
```

### Task 3: Override CSS

**Create SCSS file:**
```scss
// assets/scss/my-layout.scss
.product-detail-my-layout {
    .gallery { 
        width: 60%; 
    }
}
```

**Tell layout to use it:**
```php
class MyCustomLayout extends AbstractProductDetailLayout
{
    protected function init() {
        $this->setScssPath(__DIR__ . '/../../assets/scss/my-layout.scss');
    }
}
```

### Task 4: Conditional layouts

```php
// Different layout for VIP users
add_filter('jankx_woocommerce_current_layout', function($layout, $type) {
    if ($type === 'product-detail' && user_is_vip()) {
        return LayoutManager::getInstance()->get('vip-layout');
    }
    return $layout;
}, 10, 2);
```

### Task 5: Per-product layout

```php
// Meta box
add_action('add_meta_boxes', function() {
    add_meta_box('product_layout', 'Layout', 'render_metabox', 'product');
});

function render_metabox($post) {
    $layouts = LayoutManager::getInstance()->getByType('product-detail');
    $selected = get_post_meta($post->ID, '_layout', true);
    ?>
    <select name="product_layout">
        <?php foreach ($layouts as $layout): ?>
            <option value="<?php echo $layout->getId(); ?>" 
                    <?php selected($selected, $layout->getId()); ?>>
                <?php echo $layout->getName(); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php
}

// Save
add_action('save_post_product', function($post_id) {
    if (isset($_POST['product_layout'])) {
        update_post_meta($post_id, '_layout', $_POST['product_layout']);
    }
});

// Apply
add_filter('jankx_woocommerce_current_layout', function($layout, $type) {
    if (is_product()) {
        $custom = get_post_meta(get_the_ID(), '_layout', true);
        if ($custom) {
            return LayoutManager::getInstance()->get($custom);
        }
    }
    return $layout;
}, 10, 2);
```

## APIs Thường Dùng

### Get Managers

```php
$layoutManager = LayoutManager::getInstance();
$cssManager = CssManager::getInstance();
$settingsManager = SettingsManager::getInstance();
$provider = WooCommerceLayoutServiceProvider::getInstance();
```

### Layout Manager

```php
// Get layout
$layout = $layoutManager->get('layout-id');

// Get by type
$layouts = $layoutManager->getByType('product-detail');

// Get default
$default = $layoutManager->getDefault('product-loop');

// Register
$layoutManager->register($myLayout);
```

### Settings Manager

```php
// Get
$value = $settingsManager->get('layout-id.setting-key');

// Set
$settingsManager->set('layout-id.setting-key', $value);

// Get all for layout
$settings = $settingsManager->getLayoutSettings('layout-id');

// Generate CSS
$css = $settingsManager->generateCss('layout-id');
```

### Service Provider

```php
// Get config
$columns = $provider->get('product_loop.settings.columns');

// Get layout config
$config = $provider->getLayoutConfig('product_detail');

// Check enabled
if ($provider->isLayoutEnabled('quick_checkout')) {
    // ...
}

// Update theme option
$provider->updateThemeOption('product_loop.settings.columns', 3);
```

## Hooks Thường Dùng

### Filters

```php
// Override current layout
add_filter('jankx_woocommerce_current_layout', function($layout, $type) {
    return $layout;
}, 10, 2);

// Modify compiled CSS
add_filter('jankx_woocommerce_compiled_css', function($css) {
    return $css;
});

// Modify config
add_filter('jankx_woocommerce_config_loaded', function($config) {
    return $config;
});
```

### Actions

```php
// Register layouts
add_action('jankx_woocommerce_register_layouts', function($manager) {
    $manager->register($myLayout);
});

// After config applied
add_action('jankx_woocommerce_configuration_applied', function($config, $manager) {
    // Do something
}, 10, 2);
```

## Troubleshooting

### CSS không hiển thị

```php
// Clear cache
$cssManager = CssManager::getInstance();
$cssManager->clearCache('layout-id');

// Check SCSS compilation
$css = $cssManager->compile('/path/to/file.scss');
var_dump($css);
```

### Layout không apply

```php
// Debug current layout
add_action('wp_footer', function() {
    $bootstrap = LayoutBootstrap::getInstance();
    // Check getCurrentLayout() method
});
```

### Config không load

```php
// Check config path
$provider = WooCommerceLayoutServiceProvider::getInstance();
$config = $provider->getConfig();
var_dump($config);
```

## Next Steps

1. **Read full docs**: LAYOUT_SYSTEM.md
2. **See examples**: EXAMPLES.md
3. **Understand architecture**: LAYOUT_ARCHITECTURE.md
4. **Learn Service Provider**: SERVICE_PROVIDER.md

---

**Happy coding! 🚀**

