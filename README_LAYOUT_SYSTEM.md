# Jankx WooCommerce Layout System

## 🎯 Giới thiệu

Hệ thống Layout hoàn chỉnh cho WooCommerce được thiết kế với mục tiêu:

- ⚡ **Performance cao**: Inline CSS, không external requests, optimize cho Core Web Vitals
- 🔧 **Dễ mở rộng**: Design Patterns (Registry, Singleton, Template Method, Strategy)
- 🎨 **Linh hoạt**: Dễ dàng tạo layouts mới từ themes/plugins
- 📦 **Maintainable**: SOLID principles, clear separation of concerns
- 🔒 **Secure**: Input validation, output escaping, capability checks

## 📋 Các loại Layout

Hệ thống hỗ trợ 8 loại layouts:

1. **Product Detail** - Trang chi tiết sản phẩm
2. **Product Loop** - Danh sách sản phẩm (grid/list)
3. **Product Category Block** - Hiển thị danh mục sản phẩm
4. **Product Gallery** - Gallery ảnh sản phẩm
5. **Cart Form** - Mini cart, cart widget
6. **Cart Page** - Trang giỏ hàng
7. **Checkout Page** - Trang thanh toán
8. **Quick Checkout** - Thanh toán nhanh (custom feature)

## 🚀 Quick Start

### 1. Sử dụng Default Layouts

Layouts mặc định tự động được áp dụng khi cài đặt. Không cần cấu hình gì thêm.

### 2. Tạo Layout mới

```php
<?php
// file: themes/my-theme/includes/MyCustomLayout.php

use Jankx\WooCommerce\Abstracts\Layouts\AbstractProductDetailLayout;

class MyCustomLayout extends AbstractProductDetailLayout
{
    public function __construct()
    {
        parent::__construct(
            'my-custom-layout',
            __('My Custom Layout', 'my-theme'),
            'product-detail'
        );
    }
}

// Đăng ký layout
add_action('jankx_woocommerce_register_layouts', function($manager) {
    $manager->register(new MyCustomLayout());
});
```

### 3. Tạo SCSS cho Layout

```scss
// file: assets/scss/layouts/product-detail/my-custom-layout.scss

.product-detail-my-custom-layout {
    .gallery {
        // Your styles
    }
}
```

## 📚 Documentation

### Chi tiết documentation
- [LAYOUT_SYSTEM.md](LAYOUT_SYSTEM.md) - Hướng dẫn chi tiết
- [LAYOUT_ARCHITECTURE.md](LAYOUT_ARCHITECTURE.md) - Kiến trúc hệ thống
- [EXAMPLES.md](EXAMPLES.md) - Các ví dụ thực tế
- [llms.txt](llms.txt) - Quick reference cho AI

### API Documentation

#### LayoutManager
```php
$manager = \Jankx\WooCommerce\LayoutSystem\LayoutManager::getInstance();

// Register layout
$manager->register($layout);

// Get layout
$layout = $manager->get('layout-id');

// Get by type
$layouts = $manager->getByType('product-detail');

// Get default
$default = $manager->getDefault('product-detail');

// Set default
$manager->setDefault('product-detail', 'my-layout');
```

#### CssManager
```php
$cssManager = \Jankx\WooCommerce\LayoutSystem\CssManager::getInstance();

// Compile SCSS
$css = $cssManager->compile('/path/to/file.scss');

// Inject CSS
$cssManager->inject($css, 'layout-id');

// Clear cache
$cssManager->clearCache('layout-id');
```

#### SettingsManager
```php
$settings = \Jankx\WooCommerce\LayoutSystem\SettingsManager::getInstance();

// Get setting
$color = $settings->get('layout-id.primary_color');

// Set setting
$settings->set('layout-id.primary_color', '#ff0000');

// Get all layout settings
$allSettings = $settings->getLayoutSettings('layout-id');

// Generate CSS from settings
$css = $settings->generateCss('layout-id');
```

## 🎨 Customization

### Override Layout via Filter

```php
add_filter('jankx_woocommerce_current_layout', function($layout, $type) {
    if ($type === 'product-detail' && is_product(123)) {
        $manager = LayoutManager::getInstance();
        return $manager->get('special-layout');
    }
    return $layout;
}, 10, 2);
```

### Custom CSS Generator

```php
$settings = SettingsManager::getInstance();

$settings->registerCssGenerator('product-detail', function($layoutId, $settings) {
    $css = '';
    if (isset($settings['custom_color'])) {
        $css .= ".layout-{$layoutId} { color: {$settings['custom_color']}; }";
    }
    return $css;
});
```

## 🔧 Advanced Usage

### A/B Testing

```php
add_filter('jankx_woocommerce_current_layout', function($layout, $type) {
    if ($type === 'product-loop') {
        $variant = get_ab_test_variant(); // Your logic
        $manager = LayoutManager::getInstance();
        return $manager->get("layout-variant-{$variant}");
    }
    return $layout;
}, 10, 2);
```

### Conditional Layouts

```php
add_filter('jankx_woocommerce_current_layout', function($layout, $type) {
    // Premium users get premium layout
    if (user_is_premium()) {
        $manager = LayoutManager::getInstance();
        return $manager->get('premium-layout');
    }
    return $layout;
}, 10, 2);
```

### Per-Product Layout

```php
// Meta box để chọn layout cho product
add_action('add_meta_boxes', function() {
    add_meta_box('product_layout', 'Layout', 'render_layout_metabox', 'product');
});

// Apply selected layout
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

## 📊 Performance

### Core Web Vitals Optimization

- ✅ **LCP (Largest Contentful Paint)**: Inline CSS = no render blocking
- ✅ **FID (First Input Delay)**: Minimal JS, optimized interactions
- ✅ **CLS (Cumulative Layout Shift)**: Proper CSS, no layout shifts

### Caching Strategy

- SCSS compiled và cached in transients
- Cache invalidation on file change
- Development mode bypasses cache
- Production mode fully cached

### CSS Output

```html
<!-- Single inline <style> tag -->
<style id="jankx-woo-inline-css">
/* Minified, combined CSS for current layout */
.product-detail-default{...}
</style>
```

## 🛠️ Development

### Folder Structure

```
includes/
├── Contracts/              # Interfaces
├── Abstracts/              # Abstract base classes
├── LayoutSystem/           # Core managers
└── Layouts/                # Concrete implementations
    ├── ProductDetail/
    ├── ProductLoop/
    ├── CategoryBlock/
    ├── Gallery/
    ├── Cart/
    ├── Checkout/
    └── QuickCheckout/

assets/
└── scss/
    └── layouts/            # SCSS files
        ├── _common.scss
        ├── product-detail/
        ├── product-loop/
        └── ...
```

### Coding Standards

- Follow WordPress Coding Standards
- PSR-4 autoloading
- Type hints PHP 7.4+
- PHPDoc comments
- SOLID principles

## 🧪 Testing

### Unit Tests (Coming soon)

```bash
composer test
```

### Manual Testing

1. Enable WP_DEBUG
2. Check error logs
3. Test each layout type
4. Verify CSS output
5. Test responsive design
6. Check Core Web Vitals

## 🐛 Troubleshooting

### CSS không hiển thị

```php
// Clear cache
delete_transient('jankx_woo_css_layout_common_css_layout-id');

// Check SCSS compilation
$css = CssManager::getInstance()->compile('/path/to/scss');
var_dump($css);
```

### Layout không apply

```php
// Debug current layout
add_action('wp_footer', function() {
    $bootstrap = \Jankx\WooCommerce\LayoutSystem\LayoutBootstrap::getInstance();
    $layout = $bootstrap->getCurrentLayout();
    echo '<!-- Current Layout: ' . ($layout ? $layout->getId() : 'none') . ' -->';
});
```

## 📞 Support

- Documentation: Xem các file .md trong thư mục
- Issues: Report qua GitHub
- Questions: Contact Jankx theme support

## 📝 Changelog

### Version 1.0.0 (Initial Release)
- ✅ Complete layout system với 8 layout types
- ✅ Registry Pattern implementation
- ✅ SCSS compilation và caching
- ✅ Inline CSS injection
- ✅ Settings management
- ✅ Full documentation
- ✅ Example layouts
- ✅ Hooks và filters
- ✅ SOLID compliance
- ✅ Performance optimized

## 📄 License

GPL-2.0-or-later

## 👥 Contributors

- Jankx Team

---

**Built with ❤️ for optimal WooCommerce experience**

