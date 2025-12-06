# Category Block Filter - Transform WooCommerce Block Output

## 🎯 Mục đích

Filter này transform HTML output từ WooCommerce Product Categories block thành accordion layout format.

## 🔄 Flow

```
WooCommerce Block
    ↓ (generates nested <ul>)
HTML Output
    ↓ (filter: render_block)
CategoryBlockFilterHook
    ↓ (parse HTML)
Extract Categories
    ↓ (render with layout)
Accordion Layout
```

---

## 🚀 Enable Filter

### Method 1: Via Filter

```php
// functions.php
add_filter('jankx_woocommerce_category_block_use_accordion', '__return_true');
```

### Method 2: Via Theme Option

```php
$options = get_option('jankx_woocommerce_options', []);
$options['product_category_block']['use_accordion'] = true;
update_option('jankx_woocommerce_options', $options);
```

### Method 3: Via Config File

```php
// config/woocomerce.php
return [
    'product_category_block' => [
        'use_accordion' => true, // Enable accordion transform
        'default_layout' => 'expand-collapse-category',
    ],
];
```

---

## 📝 How It Works

### 1. HTML Input (từ WooCommerce)

```html
<ul class="wp-block-categories-list">
    <li class="cat-item cat-item-439">
        <a href="/category/electronics/">Electronics</a>
        <ul class="children">
            <li class="cat-item cat-item-440">
                <a href="/category/smartphones/">Smartphones</a>
            </li>
        </ul>
    </li>
</ul>
```

### 2. Parse & Extract

```php
[
    [
        'id' => 439,
        'name' => 'Electronics',
        'url' => '/category/electronics/',
        'is_current' => false,
        'children' => [
            [
                'id' => 440,
                'name' => 'Smartphones',
                'url' => '/category/smartphones/',
            ]
        ]
    ]
]
```

### 3. Render với Accordion Layout

```html
<div class="jankx-categories-expand-collapse">
    <div class="category-accordion-item">
        <div class="category-header">...</div>
        <div class="category-content">...</div>
    </div>
</div>
```

---

## ⚙️ Configuration

### Enable/Disable

```php
// Enable
add_filter('jankx_woocommerce_category_block_use_accordion', '__return_true');

// Disable
add_filter('jankx_woocommerce_category_block_use_accordion', '__return_false');
```

### Choose Layout

```php
// Use different layout
add_filter('jankx_woocommerce_category_block_layout', function($layoutId) {
    return 'my-custom-accordion-layout';
});
```

### Conditional Enable

```php
// Only enable on specific pages
add_filter('jankx_woocommerce_category_block_use_accordion', function($enabled) {
    if (is_front_page()) {
        return true; // Enable on homepage
    }
    return false;
});
```

---

## 🎨 Features

### Maintains Hierarchy

- ✅ Nested categories preserved
- ✅ Current category highlighted
- ✅ Current ancestors expanded
- ✅ Deep nesting supported

### Smart Parsing

- ✅ Extract category IDs từ classes
- ✅ Detect current category
- ✅ Parse URLs và names
- ✅ Recursive children parsing

### Flexible Rendering

- ✅ Support nested rendering
- ✅ Fallback to flat rendering
- ✅ Custom layout selection
- ✅ Preserve block attributes

---

## 🔧 Advanced Usage

### Custom Parser

```php
add_filter('jankx_woocommerce_parse_category_html', function($categories, $html) {
    // Custom parsing logic
    return $categories;
}, 10, 2);
```

### Modify Parsed Data

```php
add_filter('jankx_woocommerce_parsed_categories', function($categories) {
    // Add custom data
    foreach ($categories as &$cat) {
        $cat['custom_field'] = get_term_meta($cat['id'], 'custom', true);
    }
    return $categories;
});
```

### Override Layout Per Block

```php
add_filter('jankx_woocommerce_category_block_layout', function($layoutId, $block) {
    // Check block attributes
    if (isset($block['attrs']['customLayout'])) {
        return $block['attrs']['customLayout'];
    }
    return $layoutId;
}, 10, 2);
```

---

## 🐛 Debugging

### Enable Logging

```php
define('JANKX_WOO_LAYOUT_DEBUG', true);
```

### Check Logs

```bash
grep "CategoryBlockFilterHook" wp-content/debug.log
```

### Expected Logs

```
[INFO] CategoryBlockFilterHook: Accordion mode enabled
[DEBUG] CategoryBlockFilterHook: Transforming product categories block
[INFO] CategoryBlockFilterHook: Rendering with accordion layout
```

---

## 📊 Performance

- **Parsing**: Fast DOM parsing
- **Caching**: WordPress term cache used
- **Memory**: Minimal overhead
- **Execution**: < 50ms typical

---

## ✅ Checklist

Before using:

- [ ] Enable accordion mode
- [ ] Choose layout
- [ ] Test with your categories
- [ ] Check responsive design
- [ ] Verify nested categories work
- [ ] Test current category highlighting

---

## 🎯 Use Cases

1. **Transform WooCommerce block** - Tự động apply accordion
2. **Maintain hierarchy** - Keep nested structure
3. **Highlight current** - Show active category
4. **Auto-expand** - Expand current category path
5. **Custom rendering** - Use your layout

---

**Filter WooCommerce block output một cách elegant! 🎨**

