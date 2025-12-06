# 🔍 Layout Fingerprint System

## ✅ Đã Implement

Tất cả layouts trong package `jankx/woocommerce` tự động có **fingerprint** trong HTML output để dễ dàng debug và xác định layout nào đang được load.

---

## 📋 Fingerprint Format

### HTML Comment
Mỗi layout output được wrap với comment chứa thông tin fingerprint:

```html
<!-- JANKX_WOO_LAYOUT: id=flatsome-gallery | type=product-gallery | name=Flatsome Gallery | class=Jankx\WooCommerce\Layouts\Gallery\FlatsomeGalleryLayout | priority=5 | time=2025-12-05 21:30:45 -->
<div class="product-gallery product-gallery-flatsome-gallery flatsome-gallery-wrapper" 
     data-jankx-layout-id="flatsome-gallery" 
     data-jankx-layout-type="product-gallery" 
     data-jankx-layout-name="Flatsome Gallery" 
     data-jankx-layout-priority="5">
    <!-- Layout content -->
</div>
<!-- JANKX_WOO_LAYOUT: id=flatsome-gallery | type=product-gallery | name=Flatsome Gallery | class=Jankx\WooCommerce\Layouts\Gallery\FlatsomeGalleryLayout | priority=5 | time=2025-12-05 21:30:45 -->
```

### Data Attributes
Fingerprint cũng được inject vào **first wrapper element** dưới dạng data attributes:

- `data-jankx-layout-id` - Layout ID
- `data-jankx-layout-type` - Layout type
- `data-jankx-layout-name` - Layout name
- `data-jankx-layout-priority` - Layout priority

### Console Log (Tự động)
Mỗi layout tự động log vào **Browser Console** với styled output:

```javascript
console.log('[Jankx WooCommerce Layout] Flatsome Gallery', {
  id: "flatsome-gallery",
  type: "product-gallery",
  name: "Flatsome Gallery",
  class: "Jankx\WooCommerce\Layouts\Gallery\FlatsomeGalleryLayout",
  priority: 5,
  timestamp: "2025-12-05 21:30:45",
  instance: "jankx-layout-flatsome-gallery-67890abc"
});
```

---

## 🎯 Thông Tin Fingerprint

Mỗi fingerprint bao gồm:

1. **Layout ID** - Unique identifier (e.g., `flatsome-gallery`)
2. **Layout Type** - Layout category (e.g., `product-gallery`)
3. **Layout Name** - Human-readable name (e.g., `Flatsome Gallery`)
4. **Layout Class** - Full class name (e.g., `Jankx\WooCommerce\Layouts\Gallery\FlatsomeGalleryLayout`)
5. **Priority** - Layout priority (e.g., `5`)
6. **Timestamp** - Render time (e.g., `2025-12-05 21:30:45`)

---

## 🔧 Implementation

### Automatic Fingerprint
Fingerprint được tự động thêm vào tất cả layouts qua:

1. **`AbstractLayout::loadTemplate()`** - Tất cả layouts dùng template
2. **`AbstractLayout::wrapWithFingerprint()`** - Wrapper method
3. **`AbstractLayout::injectFingerprintAttributes()`** - Inject data attributes

### Manual Fingerprint
Các layouts có method render đặc biệt (không dùng `loadTemplate()`) cần gọi `wrapWithFingerprint()`:

```php
public function renderNestedCategories(array $nestedData, array $options = []): string
{
    ob_start();
    // ... render logic ...
    $output = ob_get_clean();
    
    // Add fingerprint for debugging
    return $this->wrapWithFingerprint($output);
}
```

---

## 📍 Locations

### ✅ Đã có fingerprint:

1. **AbstractLayout::loadTemplate()** ✅
   - Tất cả layouts dùng template
   - Product Detail Layouts
   - Product Loop Layouts
   - Gallery Layouts
   - Cart/Checkout Layouts

2. **ExpandCollapseCategoryLayout::renderNestedCategories()** ✅
   - Render nested categories từ HTML parse

3. **ExpandCollapseCategoryLayout::renderCategories()** ✅
   - Render categories từ WP_Term array

4. **AbstractProductCategoryBlockLayout::renderCategories()** ✅
   - Base render categories method

---

## 🔍 Cách Sử Dụng

### 1. View Source
Mở **View Page Source** và tìm:
```html
<!-- JANKX_WOO_LAYOUT:
```

### 2. Browser DevTools
Mở **Elements** tab và tìm elements có:
```html
data-jankx-layout-id="..."
```

### 3. Browser Console (Tự động)
Mỗi layout tự động log vào **Browser Console** khi render:

```
[Jankx WooCommerce Layout] Flatsome Gallery
{
  id: "flatsome-gallery",
  type: "product-gallery",
  name: "Flatsome Gallery",
  class: "Jankx\WooCommerce\Layouts\Gallery\FlatsomeGalleryLayout",
  priority: 5,
  timestamp: "2025-12-05 21:30:45",
  instance: "jankx-layout-flatsome-gallery-67890abc"
}
```

### 4. Console JavaScript (Manual)
```javascript
// Find all layouts
document.querySelectorAll('[data-jankx-layout-id]').forEach(el => {
    console.log({
        id: el.dataset.jankxLayoutId,
        type: el.dataset.jankxLayoutType,
        name: el.dataset.jankxLayoutName,
        priority: el.dataset.jankxLayoutPriority
    });
});
```

### 5. Search trong HTML
Search trong source code:
```
JANKX_WOO_LAYOUT
```

---

## 🎨 Example Output

### Flatsome Gallery Layout
```html
<!-- JANKX_WOO_LAYOUT: id=flatsome-gallery | type=product-gallery | name=Flatsome Gallery | class=Jankx\WooCommerce\Layouts\Gallery\FlatsomeGalleryLayout | priority=5 | time=2025-12-05 21:30:45 -->
<div class="product-gallery product-gallery-flatsome-gallery flatsome-gallery-wrapper" 
     data-jankx-layout-id="flatsome-gallery" 
     data-jankx-layout-type="product-gallery" 
     data-jankx-layout-name="Flatsome Gallery" 
     data-jankx-layout-priority="5">
    <!-- Gallery content -->
</div>
<script type="text/javascript">(function(){if(typeof console!=='undefined'&&console.log){console.log('%c[Jankx WooCommerce Layout]%c Flatsome Gallery','color:#4CAF50;font-weight:bold;font-size:12px;padding:2px 4px;background:#E8F5E9;border-radius:3px','color:#333;font-size:11px',{id:'flatsome-gallery',type:'product-gallery',name:'Flatsome Gallery',class:'Jankx\WooCommerce\Layouts\Gallery\FlatsomeGalleryLayout',priority:5,timestamp:'2025-12-05 21:30:45',instance:'jankx-layout-flatsome-gallery-67890abc'});}})();</script>
<!-- JANKX_WOO_LAYOUT: id=flatsome-gallery | type=product-gallery | name=Flatsome Gallery | class=Jankx\WooCommerce\Layouts\Gallery\FlatsomeGalleryLayout | priority=5 | time=2025-12-05 21:30:45 -->
```

### Expand/Collapse Category Layout
```html
<!-- JANKX_WOO_LAYOUT: id=expand-collapse-category | type=product-category-block | name=Expand/Collapse Layout | class=Jankx\WooCommerce\Layouts\CategoryBlock\ExpandCollapseCategoryLayout | priority=15 | time=2025-12-05 21:30:45 -->
<div class="jankx-categories-expand-collapse nested-structure" 
     data-animation-speed="300"
     data-jankx-layout-id="expand-collapse-category" 
     data-jankx-layout-type="product-category-block" 
     data-jankx-layout-name="Expand/Collapse Layout" 
     data-jankx-layout-priority="15">
    <!-- Category accordion content -->
</div>
<!-- JANKX_WOO_LAYOUT: id=expand-collapse-category | type=product-category-block | name=Expand/Collapse Layout | class=Jankx\WooCommerce\Layouts\CategoryBlock\ExpandCollapseCategoryLayout | priority=15 | time=2025-12-05 21:30:45 -->
```

---

## 🛠️ Methods

### AbstractLayout Methods

#### `getFingerprintData(): array`
Lấy fingerprint data:
```php
[
    'layout_id' => 'flatsome-gallery',
    'layout_type' => 'product-gallery',
    'layout_name' => 'Flatsome Gallery',
    'layout_class' => 'Jankx\WooCommerce\Layouts\Gallery\FlatsomeGalleryLayout',
    'priority' => 5,
    'timestamp' => 1701805845,
]
```

#### `generateFingerprintComment(): string`
Generate HTML comment:
```html
<!-- JANKX_WOO_LAYOUT: id=... | type=... | name=... | class=... | priority=... | time=... -->
```

#### `generateFingerprintAttributes(): string`
Generate data attributes:
```html
data-jankx-layout-id="..." data-jankx-layout-type="..." ...
```

#### `wrapWithFingerprint(string $output): string`
Wrap output với fingerprint (comment + attributes + console.log)

#### `generateConsoleLogScript(): string`
Generate inline JavaScript để log layout info vào browser console

#### `injectFingerprintAttributes(string $html): string`
Inject data attributes vào first wrapper element

---

## ✅ Benefits

1. **Easy Debugging** - Xác định layout nào đang render
2. **Performance Tracking** - Timestamp cho mỗi render
3. **CSS/JS Targeting** - Dùng data attributes để target specific layouts
4. **Development Tools** - Console scripts để list all layouts
5. **Documentation** - Self-documenting HTML

---

## 📝 Notes

- Fingerprint chỉ được thêm vào **main render output**, không thêm vào helper methods
- Data attributes chỉ inject vào **first wrapper element**
- Comment được thêm vào **đầu và cuối** output
- Timestamp là **render time**, không phải page load time

---

**Status**: ✅ **IMPLEMENTED & ACTIVE**

Tất cả layouts trong package đã có fingerprint tự động!

