# Usage Example - Expand/Collapse Category với WooCommerce Block

## 🎯 Scenario

Bạn có WooCommerce Product Categories block trong Gutenberg, muốn transform output thành accordion layout đẹp như Flatsome.

---

## 🚀 Quick Setup (3 Steps)

### Step 1: Enable Accordion Mode

**wp-config.php hoặc functions.php:**
```php
// Enable accordion transform cho WooCommerce block
add_filter('jankx_woocommerce_category_block_use_accordion', '__return_true');
```

### Step 2: Set Layout (Optional)

**config/woocomerce.php:**
```php
return [
    'product_category_block' => [
        'default_layout' => 'expand-collapse-category',
        'use_accordion' => true, // Enable transform
        'settings' => [
            'accordion_style' => 'card',
            'icon_style' => 'chevron',
            'animation_speed' => 300,
            'show_product_preview' => true,
        ],
    ],
];
```

### Step 3: Add Block trong Gutenberg

1. Thêm **Product Categories** block
2. Configure block settings (hierarchy, count, etc.)
3. Publish!

**Result**: Block HTML tự động được transform thành accordion! ✨

---

## 📋 HTML Transform Example

### Before (WooCommerce Default)

```html
<ul class="wp-block-categories-list">
    <li class="cat-item cat-item-439">
        <a href="/sach-cu-moi/">SÁCH CŨ & MỚI</a>
        <ul class="children">
            <li class="cat-item cat-item-440">
                <a href="/sach-cu/">SÁCH CŨ</a>
                <ul class="children">
                    <li class="cat-item cat-item-448">
                        <a href="/sach-danh-nhan/">Sách Danh Nhân</a>
                    </li>
                </ul>
            </li>
        </ul>
    </li>
</ul>
```

### After (Accordion Transform)

```html
<div class="jankx-categories-expand-collapse nested-structure">
    <div class="category-accordion-item depth-0">
        <div class="category-header">
            <div class="category-main">
                <img class="category-thumbnail" src="...">
                <div class="category-info">
                    <h3 class="category-title">
                        <a href="/sach-cu-moi/">SÁCH CŨ & MỚI</a>
                    </h3>
                    <span class="product-count">150 products</span>
                </div>
            </div>
            <button class="category-toggle">
                <span class="icon-expand">+</span>
                <span class="icon-collapse">-</span>
            </button>
        </div>
        <div class="category-content">
            <div class="nested-categories">
                <!-- Nested accordion items -->
            </div>
            <div class="products-preview">
                <!-- Featured products -->
            </div>
        </div>
    </div>
</div>
```

---

## 🎨 Customization

### Style Selection

```php
// Choose accordion style
add_filter('jankx_woocommerce_category_block_layout', function($layoutId) {
    // Use custom layout
    return 'my-custom-accordion';
});
```

### Conditional Transform

```php
// Only transform on homepage
add_filter('jankx_woocommerce_category_block_use_accordion', function($enabled) {
    return is_front_page();
});

// Only transform for specific blocks
add_filter('render_block', function($content, $block) {
    if ($block['blockName'] === 'woocommerce/product-categories') {
        // Check block className
        if (isset($block['attrs']['className']) && 
            strpos($block['attrs']['className'], 'use-accordion') !== false) {
            return apply_filters('jankx_woo_transform_to_accordion', $content);
        }
    }
    return $content;
}, 5, 2);
```

### Modify Parsed Data

```php
// Add custom data to categories
add_filter('jankx_woocommerce_parsed_category_data', function($categoryData) {
    // Add custom field
    $categoryData['custom_icon'] = get_term_meta($categoryData['id'], 'icon', true);
    return $categoryData;
});
```

---

## 📱 Features Maintained

Khi transform, các features của WooCommerce block vẫn được giữ:

- ✅ **Hierarchy** - Nested categories preserved
- ✅ **Current Category** - Highlighted và auto-expanded
- ✅ **Product Count** - Displayed
- ✅ **Links** - Working correctly
- ✅ **Classes** - CSS classes preserved

---

## 🔍 Debugging

### Enable Debug Logs

```php
define('JANKX_WOO_LAYOUT_DEBUG', true);
```

### Check Logs

```bash
# Filter transform logs
grep "CategoryBlockFilterHook" wp-content/debug.log

# Expected output:
# [INFO] CategoryBlockFilterHook: Accordion mode enabled
# [DEBUG] CategoryBlockFilterHook: Transforming product categories block
# [INFO] CategoryBlockFilterHook: Categories extracted | Context: {"count":5}
# [INFO] CategoryBlockFilterHook: Rendering with accordion layout
```

### Verify Transform

```php
// Add this to see if filter is working
add_action('render_block', function($content, $block) {
    if ($block['blockName'] === 'woocommerce/product-categories') {
        error_log('WooCommerce category block detected');
        error_log('Content length: ' . strlen($content));
    }
    return $content;
}, 1, 2);
```

---

## 🎯 Real-World Example

### Scenario: Shop Sidebar với Nested Categories

**1. Add Block:**
- Add "Product Categories" block vào sidebar
- Enable "Show hierarchy"
- Enable "Show product count"

**2. Enable Accordion:**
```php
// functions.php
add_filter('jankx_woocommerce_category_block_use_accordion', function($enabled) {
    return is_shop() || is_product_category(); // Only on shop pages
});
```

**3. Configure:**
```php
// config/woocomerce.php
'product_category_block' => [
    'settings' => [
        'accordion_style' => 'minimal', // Clean sidebar look
        'icon_style' => 'chevron',
        'show_product_preview' => false, // No preview in sidebar
        'show_subcategories' => true,
        'default_expanded' => false,
    ],
],
```

**Result:**
- ✅ Nested categories in accordion
- ✅ Click to expand/collapse
- ✅ Current category auto-expanded
- ✅ Clean sidebar design

---

## 💡 Tips

### Tip 1: Performance

```php
// Cache parsed categories
add_filter('jankx_woocommerce_cache_parsed_categories', '__return_true');
```

### Tip 2: Mobile Optimization

```php
// Different settings for mobile
add_filter('jankx_woocommerce_category_accordion_settings', function($settings) {
    if (wp_is_mobile()) {
        $settings['show_product_preview'] = false;
        $settings['animation_speed'] = 200;
    }
    return $settings;
});
```

### Tip 3: Custom Icons

```scss
// Custom CSS
.icon-expand::before { content: "▶"; }
.icon-collapse::before { content: "▼"; }
```

---

**Transform WooCommerce block thành accordion đẹp chỉ với vài dòng code! 🚀**

