# JavaScript Rules - Jankx WooCommerce Layout System

## 🚨 CRITICAL RULES

### Rule #1: NO External JavaScript Files

**KHÔNG BAO GIỜ**:
```html
❌ <script src="/path/to/file.js"></script>
❌ <script src="https://cdn.com/library.js"></script>
❌ wp_enqueue_script('my-script', '/js/file.js')
```

**LUÔN LUÔN**:
```html
✅ <script>
    // Inline JavaScript code here
    </script>
```

### Rule #2: Pure Vanilla JavaScript Only

**KHÔNG sử dụng**:
- ❌ jQuery (`$`, `jQuery()`)
- ❌ Lodash
- ❌ Moment.js
- ❌ Any external libraries
- ❌ Framework dependencies

**CHỈ sử dụng**:
- ✅ Pure vanilla JavaScript
- ✅ Native DOM API
- ✅ Modern ES6+ features
- ✅ Browser built-in APIs

### Rule #3: Generate via PHP

**JavaScript phải**:
- ✅ Generated trong PHP layout class
- ✅ Returned từ `renderScript()` method
- ✅ Injected inline vào HTML
- ✅ Self-contained code

---

## 📋 Implementation Pattern

### Layout Class Structure

```php
class MyLayout extends AbstractLayout
{
    /**
     * Render inline JavaScript
     *
     * @return string
     */
    protected function renderScript(): string
    {
        ob_start();
        ?>
        <script>
        (function() {
            'use strict';
            
            document.addEventListener('DOMContentLoaded', function() {
                // Your vanilla JavaScript here
                const element = document.querySelector('.my-element');
                
                element.addEventListener('click', function(e) {
                    e.preventDefault();
                    // Handle click
                });
            });
        })();
        </script>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render layout với JavaScript
     */
    public function render(array $data = []): string
    {
        $html = '<!-- HTML content -->';
        
        // Append inline JavaScript
        $html .= $this->renderScript();
        
        return $html;
    }
}
```

---

## ✅ Allowed JavaScript Features

### Modern ES6+ Syntax
```javascript
// ✅ const, let
const element = document.querySelector('.item');
let count = 0;

// ✅ Arrow functions
items.forEach(item => item.classList.add('active'));

// ✅ Template literals
const html = `<div>${title}</div>`;

// ✅ Destructuring
const {id, name} = data;

// ✅ Spread operator
const combined = [...array1, ...array2];
```

### DOM API
```javascript
// ✅ Selectors
document.querySelector('.class')
document.querySelectorAll('.items')
document.getElementById('id')

// ✅ Events
element.addEventListener('click', handler)
element.removeEventListener('click', handler)

// ✅ Classes
element.classList.add('class')
element.classList.remove('class')
element.classList.toggle('class')

// ✅ Attributes
element.getAttribute('data-id')
element.setAttribute('data-value', 'val')
element.dataset.value
```

### Modern APIs
```javascript
// ✅ Fetch API
fetch(url, options)
    .then(response => response.json())
    .then(data => console.log(data));

// ✅ Promise
new Promise((resolve, reject) => { })

// ✅ Async/Await
async function loadData() {
    const response = await fetch(url);
    const data = await response.json();
    return data;
}

// ✅ Array methods
array.map(item => item.id)
array.filter(item => item.active)
array.reduce((sum, item) => sum + item.price, 0)
```

---

## ❌ Prohibited JavaScript

### jQuery
```javascript
// ❌ NEVER use jQuery
$('.selector')
jQuery('.selector')
$('#id').click()

// ✅ Use vanilla instead
document.querySelector('.selector')
document.getElementById('id').addEventListener('click', fn)
```

### External Libraries
```javascript
// ❌ NO libraries
lodash.map()
moment().format()
axios.get()

// ✅ Use native
Array.map()
new Date().toLocaleDateString()
fetch()
```

### Global Variables
```javascript
// ❌ Avoid polluting global scope
var myGlobal = 'value';
window.myVar = 'value';

// ✅ Use IIFE (Immediately Invoked Function Expression)
(function() {
    'use strict';
    // Code here is scoped
})();
```

---

## 🎯 Common Patterns

### Pattern 1: Event Delegation
```javascript
// ✅ Efficient - One listener for multiple elements
container.addEventListener('click', function(e) {
    const button = e.target.closest('.toggle-button');
    if (!button) return;
    
    // Handle button click
});

// ❌ Inefficient - Multiple listeners
document.querySelectorAll('.toggle-button').forEach(btn => {
    btn.addEventListener('click', handler);
});
```

### Pattern 2: Slide Animation
```javascript
// ✅ Pure vanilla slide
function slideDown(element, duration) {
    element.style.removeProperty('display');
    let height = element.offsetHeight;
    
    element.style.overflow = 'hidden';
    element.style.height = 0;
    element.offsetHeight; // Force reflow
    
    element.style.transition = `height ${duration}ms ease`;
    element.style.height = height + 'px';
    
    setTimeout(() => {
        element.style.removeProperty('height');
        element.style.removeProperty('overflow');
        element.style.removeProperty('transition');
    }, duration);
}
```

### Pattern 3: AJAX Request
```javascript
// ✅ Fetch API (vanilla)
async function loadProducts(categoryId) {
    const response = await fetch('/wp-admin/admin-ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'load_products',
            category_id: categoryId,
        }),
    });
    
    return await response.json();
}

// ❌ jQuery Ajax
$.ajax({ url: '...', success: fn });
```

### Pattern 4: Dynamic Configuration
```javascript
// ✅ Read config từ data attributes
const container = document.querySelector('.layout');
const config = {
    speed: parseInt(container.dataset.speed) || 300,
    autoplay: container.dataset.autoplay === 'true',
    columns: parseInt(container.dataset.columns) || 4,
};
```

---

## 📝 JavaScript Generation in PHP

### Method 1: renderScript()
```php
protected function renderScript(): string
{
    $animationSpeed = $this->getSettingValue('animation_speed', 300);
    
    ob_start();
    ?>
    <script>
    (function() {
        'use strict';
        
        const ANIMATION_SPEED = <?php echo intval($animationSpeed); ?>;
        
        // Your vanilla JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Code here
        });
    })();
    </script>
    <?php
    return ob_get_clean();
}
```

### Method 2: Dynamic JavaScript from Settings
```php
protected function generateDynamicJavaScript(array $settings): string
{
    $js = '';
    
    if (isset($settings['enable_autoplay']) && $settings['enable_autoplay']) {
        $speed = $settings['autoplay_speed'] ?? 3000;
        
        $js .= sprintf(
            'setInterval(function() { slideNext(); }, %d);',
            intval($speed)
        );
    }
    
    return $js;
}
```

### Method 3: Conditional JavaScript
```php
public function render(array $data = []): string
{
    $html = $this->renderHtml($data);
    
    // Only add JS if needed
    if ($this->needsJavaScript()) {
        $html .= $this->renderScript();
    }
    
    return $html;
}

private function needsJavaScript(): bool
{
    return $this->hasInteractiveFeatures() || 
           $this->hasAnimations() || 
           $this->hasAjaxFeatures();
}
```

---

## 🎨 Real Examples

### Example 1: Expand/Collapse (Already Implemented)
```javascript
<script>
(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.querySelector('.jankx-categories-expand-collapse');
        if (!container) return;
        
        container.addEventListener('click', function(e) {
            const toggleBtn = e.target.closest('.category-toggle');
            if (!toggleBtn) return;
            
            e.preventDefault();
            const item = toggleBtn.closest('.category-accordion-item');
            const content = item.querySelector('.category-content');
            
            item.classList.toggle('is-expanded');
            
            if (item.classList.contains('is-expanded')) {
                slideDown(content, 300);
            } else {
                slideUp(content, 300);
            }
        });
    });
    
    // Vanilla slide utilities
    function slideDown(el, duration) { /* ... */ }
    function slideUp(el, duration) { /* ... */ }
})();
</script>
```

### Example 2: Product Gallery Slider
```javascript
<script>
(function() {
    'use strict';
    
    const gallery = document.querySelector('.product-gallery-slider');
    if (!gallery) return;
    
    let currentIndex = 0;
    const images = gallery.querySelectorAll('.gallery-image');
    const prevBtn = gallery.querySelector('.btn-prev');
    const nextBtn = gallery.querySelector('.btn-next');
    
    function showImage(index) {
        images.forEach((img, i) => {
            img.style.display = i === index ? 'block' : 'none';
        });
    }
    
    prevBtn.addEventListener('click', () => {
        currentIndex = currentIndex > 0 ? currentIndex - 1 : images.length - 1;
        showImage(currentIndex);
    });
    
    nextBtn.addEventListener('click', () => {
        currentIndex = currentIndex < images.length - 1 ? currentIndex + 1 : 0;
        showImage(currentIndex);
    });
    
    showImage(currentIndex);
})();
</script>
```

### Example 3: Quick Checkout Modal
```javascript
<script>
(function() {
    'use strict';
    
    document.addEventListener('click', function(e) {
        const quickBuyBtn = e.target.closest('.quick-buy-button');
        if (!quickBuyBtn) return;
        
        e.preventDefault();
        
        // Show modal
        const modal = document.querySelector('.quick-checkout-modal');
        const overlay = document.querySelector('.quick-checkout-overlay');
        
        modal.classList.add('active');
        overlay.classList.add('active');
        
        // Close on overlay click
        overlay.addEventListener('click', function() {
            modal.classList.remove('active');
            overlay.classList.remove('active');
        });
    });
})();
</script>
```

---

## 🔒 Security Best Practices

### Escape Output
```php
protected function renderScript(): string
{
    $categoryId = intval($this->categoryId); // Always sanitize
    $title = esc_js($this->title); // Escape for JS
    
    ob_start();
    ?>
    <script>
    const categoryId = <?php echo $categoryId; ?>;
    const title = '<?php echo $title; ?>';
    </script>
    <?php
    return ob_get_clean();
}
```

### Nonce for AJAX
```php
protected function renderScript(): string
{
    $nonce = wp_create_nonce('my-action');
    ?>
    <script>
    async function myAjaxCall() {
        const response = await fetch(ajaxurl, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                action: 'my_action',
                nonce: '<?php echo esc_js($nonce); ?>',
            }),
        });
        return await response.json();
    }
    </script>
    <?php
}
```

---

## ⚡ Performance Guidelines

### DO ✅
- ✅ Use event delegation
- ✅ Minimize DOM manipulation
- ✅ Cache selectors
- ✅ Debounce/throttle events
- ✅ Use passive event listeners
- ✅ Lazy load heavy operations

### DON'T ❌
- ❌ Attach listeners in loops
- ❌ Multiple re-flows
- ❌ Heavy operations in loops
- ❌ Global scope pollution
- ❌ Memory leaks

### Example: Optimized Code
```javascript
// ✅ Good - Event delegation
container.addEventListener('click', e => {
    const btn = e.target.closest('.btn');
    if (btn) handleClick(btn);
});

// ❌ Bad - Multiple listeners
document.querySelectorAll('.btn').forEach(btn => {
    btn.addEventListener('click', handleClick);
});

// ✅ Good - Cache selectors
const container = document.querySelector('.container');
const items = container.querySelectorAll('.item');

// ❌ Bad - Query every time
function process() {
    document.querySelector('.container').classList.add('active');
}
```

---

## 🧪 Testing JavaScript

### Verify Inline Only
```php
/** @test */
public function javascript_is_inline_not_external()
{
    $layout = new MyLayout();
    $output = $layout->render([]);
    
    // Assert có <script> tag
    $this->assertStringContainsString('<script>', $output);
    
    // Assert KHÔNG có src attribute
    $this->assertStringNotContainsString('<script src=', $output);
}
```

### Verify Pure Vanilla
```php
/** @test */
public function javascript_is_pure_vanilla()
{
    $layout = new MyLayout();
    $output = $layout->render([]);
    
    // Extract JS
    preg_match('/<script>(.*?)<\/script>/s', $output, $matches);
    $js = $matches[1] ?? '';
    
    // Assert NO jQuery
    $this->assertStringNotContainsString('jQuery(', $js);
    $this->assertStringNotContainsString('$(', $js);
    
    // Assert vanilla patterns
    $this->assertStringContainsString('document.', $js);
}
```

---

## 📚 Reference

### Common Vanilla JS Patterns

**jQuery → Vanilla Conversion**:

| jQuery | Vanilla JavaScript |
|--------|-------------------|
| `$('.class')` | `document.querySelector('.class')` |
| `$('.class').each()` | `document.querySelectorAll('.class').forEach()` |
| `$(el).addClass('c')` | `el.classList.add('c')` |
| `$(el).removeClass('c')` | `el.classList.remove('c')` |
| `$(el).toggleClass('c')` | `el.classList.toggle('c')` |
| `$(el).hasClass('c')` | `el.classList.contains('c')` |
| `$(el).show()` | `el.style.display = 'block'` |
| `$(el).hide()` | `el.style.display = 'none'` |
| `$(el).fadeIn()` | Custom fade function |
| `$(el).slideDown()` | Custom slide function |
| `$.ajax()` | `fetch()` |
| `$(document).ready()` | `DOMContentLoaded` event |

**Utility Functions**:
```javascript
// Ready event
document.addEventListener('DOMContentLoaded', fn);

// Each
elements.forEach(el => { });

// Closest
element.closest('.parent');

// Matches
element.matches('.selector');

// Ajax
fetch(url).then(r => r.json()).then(data => { });
```

---

## 🎯 Best Practices

### 1. IIFE Pattern
```javascript
// ✅ Wrap in IIFE để avoid global pollution
(function() {
    'use strict';
    
    // Your code here
    // Variables are scoped
})();
```

### 2. Strict Mode
```javascript
// ✅ Always use strict mode
'use strict';
```

### 3. Feature Detection
```javascript
// ✅ Check feature support
if ('IntersectionObserver' in window) {
    // Use IntersectionObserver
} else {
    // Fallback
}
```

### 4. Error Handling
```javascript
// ✅ Handle errors
try {
    const data = JSON.parse(response);
} catch (e) {
    console.error('Parse error:', e);
}
```

### 5. Memory Management
```javascript
// ✅ Clean up listeners
const handler = () => { };
element.addEventListener('click', handler);

// When done:
element.removeEventListener('click', handler);
```

---

## 📊 Performance Impact

### Inline JavaScript Benefits
- **Zero HTTP requests** for scripts
- **No render blocking** (async by default at footer)
- **Faster FID** (First Input Delay)
- **Better Core Web Vitals**
- **Only needed code** loaded

### Size Comparison
```
jQuery + plugins: ~100KB+
Our vanilla code: ~2-5KB per layout
Savings: 95%+ smaller
```

---

## ✅ Checklist

Before shipping JavaScript:

- [ ] Pure vanilla JavaScript only
- [ ] No jQuery or libraries
- [ ] Inline/internal only (no external files)
- [ ] Generated via PHP
- [ ] IIFE wrapped
- [ ] 'use strict' enabled
- [ ] Proper escaping
- [ ] Event delegation used
- [ ] No memory leaks
- [ ] Error handling
- [ ] Tested in browsers

---

## 🚀 Summary

**Rules**:
1. ❌ NO external JS files
2. ❌ NO jQuery or libraries
3. ✅ Pure vanilla JavaScript
4. ✅ Generate via PHP
5. ✅ Inline/internal only

**Benefits**:
- ⚡ Faster page loads
- 🎯 Better Core Web Vitals
- 📦 Smaller bundle size
- 🔒 More secure
- 🎨 Cleaner code

**Pattern**:
```
PHP Layout Class
    ↓
renderScript() method
    ↓
Generate inline <script>
    ↓
Pure vanilla JavaScript
    ↓
Self-contained, no deps
```

---

**Write modern, performant, vanilla JavaScript! 🚀**

