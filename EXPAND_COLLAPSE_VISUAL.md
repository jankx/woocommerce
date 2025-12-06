# Expand/Collapse Category Layout - Visual Guide

## 📺 Layout Preview

### Collapsed State (Default)

```
┌─────────────────────────────────────────────────────────┐
│ [IMG] Electronics          │ 150 products     │  [+]  │
├─────────────────────────────────────────────────────────┤
│ [IMG] Clothing             │ 320 products     │  [+]  │
├─────────────────────────────────────────────────────────┤
│ [IMG] Home & Garden        │ 85 products      │  [+]  │
├─────────────────────────────────────────────────────────┤
│ [IMG] Sports & Outdoors    │ 210 products     │  [+]  │
└─────────────────────────────────────────────────────────┘
```

### Expanded State (After Click)

```
┌─────────────────────────────────────────────────────────┐
│ [IMG] Electronics          │ 150 products     │  [-]  │
├─────────────────────────────────────────────────────────┤
│ ┌─ Subcategories ────────────────────────────────────┐ │
│ │  • Smartphones (45)      • Laptops (32)           │ │
│ │  • Tablets (18)          • Accessories (55)       │ │
│ └───────────────────────────────────────────────────┘ │
│ ┌─ Featured Products ────────────────────────────────┐ │
│ │  [📱]        [💻]        [⌚]        [🎧]          │ │
│ │  iPhone 14   MacBook    Apple      AirPods       │ │
│ │  $999        $1,299     Watch      Pro           │ │
│ │                         $399       $249          │ │
│ └───────────────────────────────────────────────────┘ │
│ │              View all Electronics →                │ │
└─────────────────────────────────────────────────────────┘
```

---

## 🎨 Style Variations

### 1. Default Style

```
┌──────────────────────────────┐
│ Category with border         │
└──────────────────────────────┘
```

### 2. Minimal Style

```
  Category without border
  ─────────────────────────────
```

### 3. Card Style (Elevated)

```
╔══════════════════════════════╗
║ Category with shadow         ║
╚══════════════════════════════╝
```

### 4. Bordered Style

```
╔════════════════════════════╗
║ Category thick border      ║
╚════════════════════════════╝
```

---

## 🎯 Icon Styles

### Plus/Minus (Default)

```
Collapsed: [+]
Expanded:  [-]
```

### Arrow Style

```
Collapsed: [→]
Expanded:  [↓]
```

### Chevron Style

```
Collapsed: [›]
Expanded:  [∨]
```

---

## 📱 Responsive Behavior

### Desktop (> 1024px)

```
┌──────────────────────────────────────────────────────────┐
│ [IMAGE] Category Name    │  Product Count  │  [Toggle] │
│         Description      │                 │            │
├──────────────────────────────────────────────────────────┤
│ Subcategories: [Sub1] [Sub2] [Sub3] [Sub4]              │
│ Products:     [P1]  [P2]  [P3]  [P4]                     │
└──────────────────────────────────────────────────────────┘
```

### Tablet (768px - 1024px)

```
┌────────────────────────────────────────┐
│ [IMG] Category Name    │  50   │  [+] │
├────────────────────────────────────────┤
│ Subcategories: [Sub1] [Sub2]          │
│ Products:     [P1]  [P2]               │
└────────────────────────────────────────┘
```

### Mobile (< 768px)

```
┌──────────────────────────────┐
│              [+]             │
│ [IMG]                        │
│ Category Name                │
│ 50 products                  │
├──────────────────────────────┤
│ Subcategories:               │
│ [Sub1]                       │
│ [Sub2]                       │
│                              │
│ Products:                    │
│ [P1]    [P2]                 │
└──────────────────────────────┘
```

---

## 🎬 Animation Flow

### Expand Animation

```
Step 1: Click [+]
    ↓
Step 2: Content slides down (300ms)
    ↓
Step 3: Icon changes to [-]
    ↓
Step 4: Class 'is-expanded' added
```

### Collapse Animation

```
Step 1: Click [-]
    ↓
Step 2: Content slides up (300ms)
    ↓
Step 3: Icon changes to [+]
    ↓
Step 4: Class 'is-expanded' removed
```

---

## 🎨 Color Scheme

### Default Colors

```
Background:       #ffffff
Border:           #dddddd
Hover BG:         #f9f9f9
Content BG:       #fafafa
Primary:          #0073aa
Text:             #333333
Count:            #999999
```

### Customizable via Settings

```php
'global' => [
    'primary_color' => '#your-color',
    'border_color' => '#your-border',
]
```

---

## 📐 Layout Structure

```
.jankx-categories-expand-collapse
    │
    ├── .category-accordion-item (repeats)
    │   │
    │   ├── .category-header
    │   │   ├── .category-main
    │   │   │   ├── .category-thumbnail (image)
    │   │   │   └── .category-info
    │   │   │       ├── .category-title
    │   │   │       └── .product-count
    │   │   └── .category-toggle (button)
    │   │       └── .toggle-icon
    │   │
    │   └── .category-content (expandable)
    │       ├── .subcategories-list
    │       │   └── ul.subcategories
    │       │       └── li > a
    │       │
    │       └── .products-preview
    │           ├── .products-preview-grid
    │           │   └── .product-preview-item (×4)
    │           └── .view-all-link
    │
    └── <script> (inline JavaScript)
```

---

## 🔧 Customization Points

### 1. Number of Products

```php
protected function renderProductPreview() {
    $products = wc_get_products([
        'limit' => 8, // Default: 4
    ]);
}
```

### 2. Product Grid Columns

```scss
.products-preview-grid {
    grid-template-columns: repeat(6, 1fr); // Default: 4
}
```

### 3. Animation Easing

```scss
.category-content {
    transition: all 300ms cubic-bezier(0.4, 0, 0.2, 1); // Custom easing
}
```

### 4. Thumbnail Size

```scss
.category-thumbnail {
    width: 80px;  // Default: 60px
    height: 80px;
}
```

---

## 💡 Usage Examples

### Shortcode

```
[category_accordion parent="0" limit="5" style="card"]
```

### PHP

```php
$layout = LayoutManager::getInstance()->get('expand-collapse-category');
echo $layout->render(['categories' => $categories]);
```

### Widget

```
Appearance → Widgets → Category Accordion
```

### Gutenberg Block

```
Add Block → Jankx WooCommerce → Category Accordion
```

---

## 🎯 Use Cases

1. **Homepage** - Featured categories accordion
2. **Shop Sidebar** - Category navigation
3. **Landing Pages** - Interactive category showcase
4. **Mobile Menu** - Collapsible category menu
5. **Widget Areas** - Footer category list

---

## ⚡ Performance

- **Initial Load**: Only category headers
- **On Expand**: Lazy load subcategories + products
- **Animation**: GPU-accelerated (transform/opacity)
- **Memory**: Minimal DOM nodes
- **Caching**: WordPress term cache used

---

**Beautiful, interactive, Flatsome-inspired category layout! 🎨**

