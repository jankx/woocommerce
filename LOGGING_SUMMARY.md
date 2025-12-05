# 📝 Logging System - Summary

## ✅ Đã implement hoàn chỉnh

Logging system để track package loading và debug issues.

---

## 🎯 Features

### 1. **Multi-Level Logging**
- ✅ INFO - Major events
- ✅ DEBUG - Detailed steps
- ✅ WARNING - Non-critical issues
- ✅ ERROR - Critical failures

### 2. **Multiple Output Channels**
- ✅ WordPress debug.log
- ✅ Custom log file
- ✅ Admin Bar display
- ✅ Screen dump (footer)

### 3. **Smart Enable/Disable**
- ✅ Via constant `JANKX_WOO_LAYOUT_DEBUG`
- ✅ Via URL parameter `?jankx_woo_debug=1`
- ✅ Auto-disable trong production

### 4. **Performance Tracking**
- ✅ Execution duration (ms)
- ✅ Memory usage (MB)
- ✅ File sizes (bytes)
- ✅ Event counts

### 5. **Easy Removal**
- ✅ Prefix `[JANKX_WOO_LAYOUT]` để dễ filter
- ✅ Có thể disable hoàn toàn
- ✅ Không ảnh hưởng performance khi tắt

---

## 🚀 Quick Start

### Enable Logging

**wp-config.php:**
```php
define('JANKX_WOO_LAYOUT_DEBUG', true);
```

### View Logs

**Terminal:**
```bash
tail -f wp-content/debug.log | grep "JANKX_WOO"
```

**Browser:**
```
https://your-site.com/?jankx_woo_dump_logs=1
```

**Admin Bar:**
- Login → Check Admin Bar

---

## 📊 Logged Events

### Bootstrap Phase
```
[INFO] Bootstrap: Starting Jankx WooCommerce package initialization
[DEBUG] Bootstrap: Loading WooCommerce integration file
[INFO] Bootstrap: WooCommerce integration file loaded successfully
[DEBUG] Bootstrap: LayoutBootstrap class found, initializing
[INFO] Bootstrap: LayoutBootstrap initialized
[INFO] Bootstrap: Package bootstrap completed
```

### Service Provider Phase
```
[INFO] WooCommerceServiceProvider: register() called
[DEBUG] ServiceProvider: Binding woocommerce.layout.manager
[INFO] WooCommerceServiceProvider: boot() called
[INFO] WooCommerceServiceProvider: Services booted successfully
```

### Layout Registration Phase
```
[DEBUG] LayoutManager: Registering layout
[INFO] LayoutManager: Set as default layout
[INFO] LayoutManager: Layout registered successfully
```

### CSS Compilation Phase
```
[DEBUG] CssManager: Compiling SCSS file
[DEBUG] CssManager: Cache miss, compiling from source
[INFO] CssManager: SCSS compiled successfully
[DEBUG] CssManager: Injecting layout CSS
[INFO] CssManager: Layout CSS injected
```

### Configuration Phase
```
[INFO] ConfigProvider: Loading configuration
[INFO] ConfigProvider: Configuration loaded
```

---

## 🎨 Log Format

```
[Timestamp] [Level] [JANKX_WOO_LAYOUT] Message | Context: {JSON}
```

**Example:**
```
2025-12-05 10:30:45 [INFO] [JANKX_WOO_LAYOUT] LayoutManager: Layout registered successfully | Context: {"layout_id":"grid-product-loop","total_layouts":3}
```

---

## 🔍 Debug Scenarios

### 1. Package không load?

**Check:**
```bash
grep "Bootstrap: Starting" wp-content/debug.log
```

**Expected:**
```
[INFO] Bootstrap: Starting Jankx WooCommerce package initialization
[INFO] Bootstrap: Package bootstrap completed
```

### 2. Layout không register?

**Check:**
```bash
grep "LayoutManager: Registering" wp-content/debug.log
```

**Look for:**
- Layout ID
- Registration success/failure
- Total layouts count

### 3. CSS không compile?

**Check:**
```bash
grep "CssManager:" wp-content/debug.log
```

**Look for:**
- File paths
- Compilation errors
- Cache status

### 4. Performance issues?

**Check:**
```bash
grep "duration_ms" wp-content/debug.log
```

**Look for:**
- Slow operations (> 100ms)
- Memory spikes
- Large file sizes

---

## 🧹 Cleanup

### Disable Logging

```php
// wp-config.php
define('JANKX_WOO_LAYOUT_DEBUG', false);
```

### Clear Logs

```bash
# Clear debug.log
> wp-content/debug.log

# Clear custom log
rm wp-content/logs/jankx-woo-layout.log
```

### Remove Logger Calls (Production)

Tất cả Logger calls có thể được comment out hoặc remove:

```bash
# Find all logger calls
grep -r "Logger::" includes/ | wc -l

# Comment out (if needed)
find includes/ -name "*.php" -exec sed -i 's/Logger::/\/\/ Logger::/g' {} \;
```

---

## 📈 Performance Impact

### When Enabled
- Minimal overhead (< 1ms per log)
- Memory: ~100 bytes per log entry
- No database queries

### When Disabled
- **Zero overhead** - Checks constant only
- Early return if disabled
- No performance impact

---

## 🎓 Best Practices

### DO ✅
- ✅ Enable trong development
- ✅ Log major events
- ✅ Include context data
- ✅ Use appropriate levels
- ✅ Disable trong production

### DON'T ❌
- ❌ Log sensitive data
- ❌ Log trong loops
- ❌ Over-log trivial operations
- ❌ Leave enabled trong production
- ❌ Log user passwords/keys

---

## 📚 Documentation

- **LOGGING.md** - Complete logging guide
- **DEBUG.md** - Quick reference
- **wp-config-example.php** - Configuration examples

---

## ✨ Summary

**Logging system provides:**
- 🔍 Complete visibility vào package loading
- 📊 Performance metrics
- 🐛 Easy debugging
- 🧹 Easy cleanup
- ⚡ Zero overhead khi disabled

**Perfect cho:**
- Development debugging
- Performance monitoring
- Issue troubleshooting
- Production monitoring (temporary)

---

**Log smart, debug fast! 🚀**

