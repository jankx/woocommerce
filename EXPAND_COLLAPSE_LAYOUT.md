# Expand/Collapse Category Layout

## 🎨 Giới thiệu

Layout theo style Flatsome theme với tính năng expand/collapse cho product categories. Cho phép users click vào category để xem subcategories và featured products.

## ✨ Features

- ✅ **Expand/Collapse Animation** - Smooth slide animation
- ✅ **Subcategories Display** - Show child categories khi expand
- ✅ **Product Preview** - Show 4 featured products từ category
- ✅ **Multiple Styles** - Minimal, Bordered, Card
- ✅ **Customizable Icons** - Plus/Minus, Arrow, Chevron
- ✅ **Responsive Design** - Mobile-friendly
- ✅ **Keyboard Accessible** - Support keyboard navigation
- ✅ **No jQuery** - Pure vanilla JavaScript

---

## 🚀 Usage

### Register Layout

Layout tự động được register, không cần làm gì thêm.

### Set as Default

```php
// Via filter
add_filter('jankx_woocommerce_current_layout', function($layout, $type) {
    if ($type === 'product-category-block') {
        $manager = \Jankx\WooCommerce\LayoutSystem\LayoutManager::getInstance();
        return $manager->get('expand-collapse-category');
    }
    return $layout;
}, 10, 2);
```

### Via Config File

```php
// config/woocomerce.php
return [
    'product_category_block' => [
        'default_layout' => 'expand-collapse-category',
        'settings' => [
            'show_subcategories' => true,
            'show_product_preview' => true,
            'default_expanded' => false,
            'animation_speed' => 300,
            'accordion_style' => 'card', // minimal, bordered, card
            'icon_style' => 'plus-minus', // plus-minus, arrow, chevron
        ],
    ],
];
```

---

## 🎨 Styles

### 1. Default Style (Bordered)

```scss
.category-accordion-item {
    border: 1px solid #ddd;
    border-radius: 5px;
}
```

### 2. Minimal Style

```php
'accordion_style' => 'minimal'
```

```scss
.category-accordion-item {
    border: none;
    border-bottom: 1px solid #eee;
}
```

### 3. Card Style (Elevated)

```php
'accordion_style' => 'card'
```

```scss
.category-accordion-item {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 15px;
}
```

---

## 🎯 Icon Styles

### Plus/Minus (Default)

```
Collapsed: +
Expanded: -
```

### Arrow

```
Collapsed: →
Expanded: ↓
```

### Chevron

```
Collapsed: ›
Expanded: ∨
```

---

## ⚙️ Settings

### Available Settings

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| `show_subcategories` | bool | true | Show child categories |
| `show_product_preview` | bool | true | Show featured products |
| `default_expanded` | bool | false | Default state |
| `animation_speed` | int | 300 | Animation duration (ms) |
| `accordion_style` | string | 'default' | Visual style |
| `icon_style` | string | 'plus-minus' | Toggle icon style |

### Example Configuration

```php
$settings = [
    'show_subcategories' => true,
    'show_product_preview' => true,
    'default_expanded' => false,
    'animation_speed' => 250,
    'accordion_style' => 'card',
    'icon_style' => 'chevron',
];

$settingsManager = \Jankx\WooCommerce\LayoutSystem\SettingsManager::getInstance();
$settingsManager->setLayoutSettings('expand-collapse-category', $settings);
```

---

## 🎬 Behavior

### Click to Expand

1. User clicks category header hoặc toggle button
2. Smooth slide animation
3. Show subcategories (nếu có)
4. Show 4 featured products
5. Icon changes từ + sang -

### Click to Collapse

1. User clicks lại
2. Smooth slide animation
3. Hide content
4. Icon changes từ - sang +

### Keyboard Navigation

- `Tab` - Navigate giữa categories
- `Enter` hoặc `Space` - Toggle expand/collapse

---

## 📱 Responsive

### Desktop (> 1024px)
- Full layout với thumbnail 60x60
- 4 product previews trong grid
- Full spacing

### Tablet (768px - 1024px)
- Thumbnail 50x50
- 2 product previews
- Reduced spacing

### Mobile (< 768px)
- Vertical layout
- 2 product previews
- Compact spacing
- Toggle button positioned absolutely

---

## 🎨 Customization

### Custom CSS

```css
/* Change animation speed */
.category-accordion-item .category-content {
    transition: all 500ms ease;
}

/* Custom colors */
.category-accordion-item {
    border-color: #your-color;
}

.category-toggle:hover {
    background-color: #your-primary-color;
}

/* Custom thumbnail size */
.category-thumbnail {
    width: 80px;
    height: 80px;
}
```

### Override Template

Copy template vào theme:

```
themes/your-theme/jankx-woocommerce/layouts/expand-collapse-category/category-item.php
```

### Custom JavaScript

```javascript
// Add custom event listener
document.addEventListener('jankx-category-expanded', function(e) {
    console.log('Category expanded:', e.detail.categoryId);
});

document.addEventListener('jankx-category-collapsed', function(e) {
    console.log('Category collapsed:', e.detail.categoryId);
});
```

---

## 🔧 Advanced

### Load More Products

```php
class CustomExpandCollapseLayout extends ExpandCollapseCategoryLayout
{
    protected function renderProductPreview(\WP_Term $category): string
    {
        // Get 8 products instead of 4
        $products = wc_get_products([
            'category' => [$category->slug],
            'limit' => 8,
        ]);
        
        // Custom rendering
        ob_start();
        // ... your code
        return ob_get_clean();
    }
}
```

### Add Ajax Loading

```javascript
// Load products via AJAX when expand
jQuery('.category-toggle').on('click', function() {
    const categoryId = $(this).closest('.category-accordion-item').data('category-id');
    
    $.ajax({
        url: wc_ajax_url,
        data: {
            action: 'load_category_products',
            category_id: categoryId
        },
        success: function(response) {
            // Inject products
        }
    });
});
```

---

## 🐛 Troubleshooting

### Animation không smooth

Check animation speed setting:
```php
$settings['animation_speed'] = 300; // Try different values
```

### Subcategories không hiện

```php
// Check setting
$settings['show_subcategories'] = true;

// Check if category has children
$children = get_term_children($category->term_id, 'product_cat');
var_dump($children);
```

### Products không load

```php
// Check WooCommerce function available
if (!function_exists('wc_get_products')) {
    // WooCommerce not active
}
```

---

## 📊 Performance

- **Initial Load**: Chỉ render category headers
- **On Expand**: Load subcategories + products
- **Cached**: Category children cached by WordPress
- **Optimized**: Minimal DOM manipulation

---

## 🎓 Inspired by Flatsome

Layout này được inspired bởi Flatsome theme's category accordion, với improvements:
- ✅ Better animation
- ✅ More customization options
- ✅ Cleaner code
- ✅ No jQuery dependency
- ✅ Full test coverage

---

**Enjoy beautiful, interactive category layouts! 🎨**

