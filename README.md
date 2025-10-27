# Jankx WooCommerce Integration

Package tích hợp WooCommerce với Jankx Post Layout system.

## Kiến trúc

### Code Isolation
- **Core (`cheephub`)**: Không biết về WooCommerce
- **Vendor (`cheephub-v1/vendor/jankx/woocommerce`)**: WooCommerce-specific code
- **Kết nối qua WordPress Filters**: Hook system để tách biệt concerns

### Components

1. **WooCommerceContentGenerator** (`includes/PostLayout/WooCommerceContentGenerator.php`)
   - Generator riêng cho WooCommerce products
   - Sử dụng WC templates
   
2. **WooCommercePostLayoutHook** (`includes/Hooks/WooCommercePostLayoutHook.php`)
   - Hook vào `jankx/post-layout/generator` filter
   - Auto-detect và apply generator cho product post type
   
3. **WooCommerce** (`includes/WooCommerce.php`)
   - Main integration class
   - Initialize hooks

## Cách hoạt động

### Flow
```
PostLayoutDecorator::withAttributes()
  ↓
apply_filters('jankx/post-layout/generator')
  ↓
WooCommercePostLayoutHook::provideGenerator() (in vendor)
  ↓
Returns WooCommerceContentGenerator instance
  ↓
PostLayoutDecorator applies generator to layout
```

### Tự động apply
Khi Post Type Layout block có `postType = 'product'`:
1. WooCommercePostLayoutHook detect product post type
2. Return WooCommerceContentGenerator
3. Generator tự động được áp dụng

## Lợi ích Code Isolation

✅ **Core không phụ thuộc**: `cheephub` core không import WooCommerce classes  
✅ **Vendor độc lập**: WooCommerce code ở riêng trong vendor package  
✅ **Loosely coupled**: Kết nối qua WordPress filters  
✅ **Easy to disable**: Đơn giản tắt bằng cách remove filter  

## Sử dụng

### Automatic (mặc định)
Tự động apply khi post type là `product`:
```php
// Trong Gutenberg block
$attributes['postType'] = 'product';
// WooCommerce generator tự động apply
```

### Manual
```php
use Jankx\WooCommerce\PostLayout\WooCommerceContentGenerator;

$layout = new GridLayout();
$layout->setContentGenerator(new WooCommerceContentGenerator());
```

## Customize

Để customize template, tạo file trong theme:
- `woocommerce/content-product.php` - Product item template

## Lưu ý

1. WooCommerce phải active
2. Hook tự động register khi `WooCommerce::init()` được gọi
3. Code hoàn toàn tách biệt, không violate isolation