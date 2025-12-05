# Jankx WooCommerce Layout System

## Tổng quan

Hệ thống Layout System cho WooCommerce được thiết kế với mục tiêu:
- **Hiệu năng cao**: Inline CSS, không có external CSS thừa, optimize cho Core Web Vitals
- **Linh hoạt**: Dễ dàng tạo và đăng ký layouts mới từ themes/plugins
- **Maintainable**: Sử dụng Design Patterns (Registry, Singleton, Template Method)
- **Extensible**: Interface và Abstract classes rõ ràng cho polymorphism

## Kiến trúc

### 1. Design Patterns

#### Registry Pattern
`LayoutManager` sử dụng Registry Pattern để quản lý tất cả layouts:
```php
$layoutManager = LayoutManager::getInstance();
$layoutManager->register($myLayout);
$layout = $layoutManager->get('my-layout-id');
```

#### Singleton Pattern
Tất cả các Manager classes (LayoutManager, CssManager, SettingsManager) đều là Singleton:
```php
$cssManager = CssManager::getInstance();
$settingsManager = SettingsManager::getInstance();
```

#### Template Method Pattern
Abstract classes định nghĩa skeleton của layouts, child classes implement chi tiết:
```php
abstract class AbstractProductDetailLayout {
    // Template method
    public function render(array $data) {
        // Calls hook methods
        $this->renderGallery();
        $this->renderSummary();
    }
    
    // Hook methods - override in child
    abstract protected function renderGallery();
}
```

### 2. Cấu trúc thư mục

```
includes/
├── Contracts/              # Interfaces
│   ├── LayoutInterface.php
│   ├── CssManagerInterface.php
│   ├── LayoutManagerInterface.php
│   ├── SettingsInterface.php
│   └── Layouts/           # Layout-specific interfaces
│       ├── ProductDetailLayoutInterface.php
│       ├── ProductLoopLayoutInterface.php
│       ├── CartPageLayoutInterface.php
│       └── ...
├── Abstracts/             # Abstract base classes
│   ├── AbstractLayout.php
│   ├── AbstractCssManager.php
│   └── Layouts/           # Layout-specific abstracts
│       ├── AbstractProductDetailLayout.php
│       ├── AbstractProductLoopLayout.php
│       └── ...
├── LayoutSystem/          # Core system
│   ├── LayoutManager.php
│   ├── CssManager.php
│   ├── SettingsManager.php
│   └── LayoutBootstrap.php
└── Layouts/               # Concrete implementations
    ├── ProductDetail/
    │   └── DefaultProductDetailLayout.php
    ├── ProductLoop/
    │   ├── GridProductLoopLayout.php
    │   └── ListProductLoopLayout.php
    ├── CategoryBlock/
    ├── Gallery/
    ├── Cart/
    ├── Checkout/
    └── QuickCheckout/
```

## Các loại Layout

### 1. Product Detail Layout
Dành cho trang chi tiết sản phẩm.

**Interface**: `ProductDetailLayoutInterface`
**Abstract**: `AbstractProductDetailLayout`
**Default**: `DefaultProductDetailLayout`

**Methods chính**:
- `renderGallery(WC_Product $product)`
- `renderSummary(WC_Product $product)`
- `renderMeta(WC_Product $product)`
- `renderTabs(WC_Product $product)`
- `renderRelatedProducts(WC_Product $product)`

### 2. Product Loop Layout
Dành cho danh sách sản phẩm (shop, archive, category).

**Interface**: `ProductLoopLayoutInterface`
**Abstract**: `AbstractProductLoopLayout`
**Defaults**: 
- `GridProductLoopLayout` (grid)
- `ListProductLoopLayout` (list)

**Methods chính**:
- `renderProduct(WC_Product $product, array $options)`
- `renderThumbnail(WC_Product $product)`
- `renderTitle(WC_Product $product)`
- `renderPrice(WC_Product $product)`
- `renderBadges(WC_Product $product)`

### 3. Product Category Block Layout
Dành cho hiển thị danh sách categories.

**Interface**: `ProductCategoryBlockLayoutInterface`
**Abstract**: `AbstractProductCategoryBlockLayout`
**Default**: `GridCategoryBlockLayout`

### 4. Product Gallery Layout
Dành cho gallery ảnh sản phẩm.

**Interface**: `ProductGalleryLayoutInterface`
**Abstract**: `AbstractProductGalleryLayout`
**Default**: `SliderGalleryLayout`

### 5. Cart Form Layout
Dành cho mini cart, cart widget.

**Interface**: `CartFormLayoutInterface`
**Abstract**: `AbstractCartFormLayout`

### 6. Cart Page Layout
Dành cho trang giỏ hàng.

**Interface**: `CartPageLayoutInterface`
**Abstract**: `AbstractCartPageLayout`
**Default**: `DefaultCartPageLayout`

### 7. Checkout Page Layout
Dành cho trang checkout.

**Interface**: `CheckoutPageLayoutInterface`
**Abstract**: `AbstractCheckoutPageLayout`
**Defaults**:
- `DefaultCheckoutLayout` (two-column)
- `MultiStepCheckoutLayout` (multi-step)

### 8. Quick Checkout Layout
Dành cho quick checkout (custom feature).

**Interface**: `QuickCheckoutLayoutInterface`
**Abstract**: `AbstractQuickCheckoutLayout`
**Default**: `ModalQuickCheckoutLayout`

## CSS Management

### SCSS Compilation
Hệ thống tự động compile SCSS sang CSS:
```php
$cssManager = CssManager::getInstance();
$css = $cssManager->compile('/path/to/layout.scss');
```

### Inline CSS Injection
CSS được inject trực tiếp vào HTML (không external file):
```php
$cssManager->inject($css, 'layout-id');
```

### Dynamic CSS từ Settings
CSS động được generate dựa trên user settings:
```php
protected function generateDynamicCss(array $settings): string {
    $css = '';
    if (isset($settings['primary_color'])) {
        $css .= ".layout { color: {$settings['primary_color']}; }";
    }
    return $css;
}
```

### Cache
CSS compiled được cache để tối ưu performance:
```php
$cssManager->cache('cache-key', $css, 3600);
$cached = $cssManager->getCached('cache-key');
```

## Settings Management

### Lưu Settings
```php
$settingsManager = SettingsManager::getInstance();
$settingsManager->set('layout-id.primary_color', '#ff0000');
$settingsManager->setLayoutSettings('layout-id', [
    'primary_color' => '#ff0000',
    'spacing' => 20,
]);
```

### Đọc Settings
```php
$color = $settingsManager->get('layout-id.primary_color');
$allSettings = $settingsManager->getLayoutSettings('layout-id');
```

### Generate CSS từ Settings
```php
$css = $settingsManager->generateCss('layout-id');
```

## Tạo Layout mới

### 1. Từ Theme

```php
// file: functions.php hoặc includes/woocommerce-layouts.php

use Jankx\WooCommerce\Abstracts\Layouts\AbstractProductDetailLayout;

class MyCustomProductLayout extends AbstractProductDetailLayout
{
    public function __construct()
    {
        parent::__construct(
            'my-custom-layout',
            __('My Custom Layout', 'my-theme'),
            'product-detail'
        );
        
        $this->supportedSettings = ['custom_option'];
    }
    
    // Override methods nếu cần
    public function renderGallery(WC_Product $product): string
    {
        // Custom implementation
        return '<div class="custom-gallery">...</div>';
    }
}

// Đăng ký layout
add_action('jankx_woocommerce_register_layouts', function($manager) {
    $manager->register(new MyCustomProductLayout());
});

// Set làm default (optional)
add_filter('jankx_woocommerce_current_layout', function($layout, $type) {
    if ($type === 'product-detail') {
        $manager = \Jankx\WooCommerce\LayoutSystem\LayoutManager::getInstance();
        return $manager->get('my-custom-layout');
    }
    return $layout;
}, 10, 2);
```

### 2. Từ Plugin

```php
// file: plugin-main.php

add_action('jankx_woocommerce_register_layouts', function($manager) {
    // Register multiple layouts
    $manager->register(new MyPluginLayout1());
    $manager->register(new MyPluginLayout2());
});
```

### 3. Tạo SCSS file cho Layout

Tạo file SCSS tại:
```
assets/scss/layouts/{type}/{layout-id}.scss
```

Ví dụ: `assets/scss/layouts/product-detail/my-custom-layout.scss`

```scss
// Common SCSS cho layout
.product-detail-my-custom-layout {
    .product-gallery {
        display: flex;
        gap: 20px;
    }
    
    .product-summary {
        flex: 1;
    }
    
    @media (max-width: 768px) {
        flex-direction: column;
    }
}
```

## Hooks và Filters

### Actions

#### `jankx_woocommerce_register_default_layouts`
Fired khi đăng ký default layouts.
```php
add_action('jankx_woocommerce_register_default_layouts', function($manager) {
    // Register layouts
});
```

#### `jankx_woocommerce_register_layouts`
Fired để cho phép themes/plugins đăng ký layouts.
```php
add_action('jankx_woocommerce_register_layouts', function($manager) {
    $manager->register($myLayout);
});
```

#### `jankx_woocommerce_layout_registered`
Fired sau khi một layout được đăng ký.
```php
add_action('jankx_woocommerce_layout_registered', function($layout, $manager) {
    // Do something
}, 10, 2);
```

### Filters

#### `jankx_woocommerce_allow_layout_override`
Cho phép override layout đã tồn tại.
```php
add_filter('jankx_woocommerce_allow_layout_override', function($allow, $layoutId, $layout) {
    if ($layoutId === 'specific-layout') {
        return true;
    }
    return $allow;
}, 10, 3);
```

#### `jankx_woocommerce_current_layout`
Override layout hiện tại dựa trên context.
```php
add_filter('jankx_woocommerce_current_layout', function($layout, $type) {
    if (is_product() && get_post_meta(get_the_ID(), '_custom_layout', true)) {
        $manager = LayoutManager::getInstance();
        return $manager->get('custom-layout-id');
    }
    return $layout;
}, 10, 2);
```

#### `jankx_woocommerce_compiled_css`
Modify CSS sau khi compile.
```php
add_filter('jankx_woocommerce_compiled_css', function($css) {
    // Add custom CSS or modify
    return $css;
});
```

#### `jankx_woocommerce_scss_import_paths`
Thêm import paths cho SCSS compiler.
```php
add_filter('jankx_woocommerce_scss_import_paths', function($paths) {
    $paths[] = get_stylesheet_directory() . '/scss';
    return $paths;
});
```

## Best Practices

### 1. Tối ưu Performance
- Luôn cache compiled CSS
- Chỉ inject CSS cần thiết cho page hiện tại
- Minify CSS trong production
- Sử dụng SCSS variables và mixins để tái sử dụng

### 2. Code Organization
- Mỗi layout nên có file riêng
- Sử dụng namespaces phù hợp
- Document các methods public
- Follow WordPress Coding Standards

### 3. Settings Validation
- Luôn validate user input
- Sử dụng sanitization functions
- Provide default values

### 4. Extensibility
- Provide hooks và filters
- Allow themes override templates
- Use dependency injection khi có thể

## Examples

### Example 1: Custom Product Grid với Hover Effect

```php
class HoverEffectGridLayout extends AbstractProductLoopLayout
{
    public function __construct()
    {
        parent::__construct(
            'hover-effect-grid',
            __('Hover Effect Grid', 'my-theme'),
            'product-loop'
        );
        
        $this->supportedSettings = ['hover_type', 'animation_speed'];
    }
    
    protected function generateDynamicCss(array $settings): string
    {
        $css = parent::generateDynamicCss($settings);
        
        $hoverType = $settings['hover_type'] ?? 'zoom';
        $speed = $settings['animation_speed'] ?? 0.3;
        
        if ($hoverType === 'zoom') {
            $css .= "
                .product-item:hover img {
                    transform: scale(1.1);
                    transition: transform {$speed}s ease;
                }
            ";
        }
        
        return $css;
    }
}
```

### Example 2: Multi-Currency Price Display

```php
class MultiCurrencyProductLoop extends GridProductLoopLayout
{
    public function renderPrice(WC_Product $product): string
    {
        $price = parent::renderPrice($product);
        
        // Add additional currencies
        $currencies = ['USD', 'EUR', 'JPY'];
        foreach ($currencies as $currency) {
            $converted = $this->convertPrice($product->get_price(), $currency);
            $price .= sprintf('<span class="alt-price">%s</span>', $converted);
        }
        
        return $price;
    }
    
    private function convertPrice($price, $currency)
    {
        // Conversion logic
        return wc_price($price, ['currency' => $currency]);
    }
}
```

## Troubleshooting

### CSS không hiển thị
1. Check cache: Clear transients
2. Check SCSS compilation errors (enable WP_DEBUG)
3. Verify file permissions
4. Check if layout được register đúng

### Layout không apply
1. Check layout ID đúng chưa
2. Verify hook timing
3. Check conditional logic (is_product(), etc.)
4. Clear object cache nếu dùng

### Performance Issues
1. Enable CSS caching
2. Use production mode (disable WP_DEBUG)
3. Optimize SCSS (remove unused code)
4. Consider using critical CSS

## Support

For issues và questions, please contact Jankx theme support.

