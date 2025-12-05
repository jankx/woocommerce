# Logging Guide - Jankx WooCommerce Layout System

## 🔍 Enable Debug Logging

Logging giúp theo dõi package load và debug issues.

### Method 1: Via wp-config.php (Recommended)

Thêm vào `wp-config.php`:

```php
// Enable Jankx WooCommerce Layout debug logging
define('JANKX_WOO_LAYOUT_DEBUG', true);
```

### Method 2: Via URL Parameter

Khi `WP_DEBUG = true`, thêm query param:

```
https://your-site.com/?jankx_woo_debug=1
```

### Method 3: Custom Log File

```php
// wp-config.php
define('JANKX_WOO_LAYOUT_DEBUG', true);
define('JANKX_WOO_LAYOUT_LOG_FILE', WP_CONTENT_DIR . '/debug-jankx-woo.log');
```

---

## 📋 What Gets Logged

### Bootstrap Events

```
[INFO] Bootstrap: Starting Jankx WooCommerce package initialization
[DEBUG] Bootstrap: Loading WooCommerce integration file
[INFO] Bootstrap: WooCommerce integration file loaded successfully
[DEBUG] Bootstrap: LayoutBootstrap class found, registering init hook
[INFO] Bootstrap: Initializing LayoutBootstrap
[INFO] Bootstrap: LayoutBootstrap initialized successfully
[INFO] Bootstrap: Package bootstrap completed
```

### LayoutBootstrap Events

```
[INFO] LayoutBootstrap: Constructor called
[DEBUG] LayoutBootstrap: LayoutManager initialized
[DEBUG] LayoutBootstrap: CssManager initialized
[DEBUG] LayoutBootstrap: SettingsManager initialized
[DEBUG] LayoutBootstrap: initServiceProvider called
[INFO] LayoutBootstrap: Service providers registered
[INFO] LayoutBootstrap: Initialization complete
```

### Service Provider Events

```
[INFO] WooCommerceServiceProvider: register() called
[DEBUG] ServiceProvider: Binding woocommerce.layout.manager
[DEBUG] ServiceProvider: Binding woocommerce.layout.css
[DEBUG] ServiceProvider: Binding woocommerce.layout.settings
[INFO] WooCommerceServiceProvider: boot() called
[INFO] WooCommerceServiceProvider: Services booted successfully
```

### Layout Registration

```
[DEBUG] LayoutManager: Registering layout
[INFO] LayoutManager: Set as default layout
[INFO] LayoutManager: Layout registered successfully
```

### CSS Compilation

```
[DEBUG] CssManager: Compiling SCSS file
[DEBUG] CssManager: Cache miss, compiling from source
[INFO] CssManager: SCSS compiled successfully
[DEBUG] CssManager: Injecting layout CSS
[INFO] CssManager: Layout CSS injected
```

### Configuration Loading

```
[INFO] ConfigProvider: Loading configuration
[INFO] ConfigProvider: Configuration loaded
```

---

## 🔧 Log Levels

### INFO
- Package bootstrap events
- Layout registration
- CSS compilation success
- Configuration loading
- Major milestones

### DEBUG
- Detailed step-by-step execution
- Cache hits/misses
- Service bindings
- Internal operations

### WARNING
- Duplicate layout IDs
- Files not found (non-critical)
- Fallback scenarios

### ERROR
- Critical failures
- Missing required files
- Compilation errors
- Configuration errors

---

## 📊 Viewing Logs

### Method 1: WordPress debug.log

Logs xuất hiện trong `wp-content/debug.log`:

```bash
tail -f wp-content/debug.log | grep "JANKX_WOO"
```

### Method 2: Custom Log File

Nếu set `JANKX_WOO_LAYOUT_LOG_FILE`:

```bash
tail -f wp-content/debug-jankx-woo.log
```

### Method 3: Admin Bar

Khi logged in as admin, logs xuất hiện trong Admin Bar:

```
Admin Bar → "Jankx WooCommerce Logs (25)"
```

### Method 4: Dump Logs

Thêm query param để dump logs ra footer:

```
https://your-site.com/?jankx_woo_dump_logs=1
```

---

## 🎯 Common Debugging Scenarios

### Scenario 1: Package không load

**Enable logging:**
```php
define('JANKX_WOO_LAYOUT_DEBUG', true);
```

**Check logs:**
```bash
grep "Bootstrap:" wp-content/debug.log
```

**Expected:**
```
[INFO] Bootstrap: Starting Jankx WooCommerce package initialization
[INFO] Bootstrap: Package bootstrap completed
```

### Scenario 2: Layout không register

**Check logs:**
```bash
grep "LayoutManager: Registering" wp-content/debug.log
```

**Expected:**
```
[DEBUG] LayoutManager: Registering layout | Context: {"layout_id":"grid-product-loop",...}
[INFO] LayoutManager: Layout registered successfully
```

### Scenario 3: CSS không compile

**Check logs:**
```bash
grep "CssManager: Compiling" wp-content/debug.log
```

**Look for:**
```
[ERROR] CssManager: SCSS compilation failed
```

### Scenario 4: Service Provider không boot

**Check logs:**
```bash
grep "ServiceProvider:" wp-content/debug.log
```

**Expected:**
```
[INFO] WooCommerceServiceProvider: register() called
[INFO] WooCommerceServiceProvider: boot() called
```

---

## 🧹 Disable/Remove Logs

### Temporary Disable

```php
// wp-config.php
define('JANKX_WOO_LAYOUT_DEBUG', false);
```

### Permanent Remove (Production)

Search và remove tất cả `Logger::` calls:

```bash
cd vendor/jankx/woocommerce

# Preview what will be removed
grep -r "Logger::" includes/ | head -20

# Comment out logger calls (recommended)
find includes/ -name "*.php" -exec sed -i 's/Logger::/\/\/ Logger::/g' {} \;
```

Hoặc tạo version production không có logs.

---

## 📝 Log Format

### Standard Format

```
[Timestamp] [Level] [JANKX_WOO_LAYOUT] Message | Context: {JSON}
```

### Example

```
2025-12-05 10:30:45 [INFO] [JANKX_WOO_LAYOUT] LayoutManager: Layout registered successfully | Context: {"layout_id":"grid-product-loop","total_layouts":3}
```

---

## 🎨 Custom Logging

### Add Custom Logs

```php
use Jankx\WooCommerce\Helpers\Logger;

// In your custom code
Logger::info('My custom event', [
    'data' => $someData,
    'user_id' => get_current_user_id(),
]);

Logger::debug('Detailed info', ['step' => 1]);
Logger::warning('Something suspicious', ['value' => $value]);
Logger::error('Critical error', ['error' => $e->getMessage()]);
```

### Get Logs Programmatically

```php
// Get all logs
$allLogs = Logger::getLogs();

// Get by level
$errors = Logger::getLogsByLevel('ERROR');
$warnings = Logger::getLogsByLevel('WARNING');

// Dump to screen (debug only)
Logger::dump();
```

---

## 🛡️ Best Practices

### DO ✅
- ✅ Enable logging trong development
- ✅ Log major events (bootstrap, registration, compilation)
- ✅ Include context data
- ✅ Use appropriate log levels
- ✅ Clear logs sau khi debug xong

### DON'T ❌
- ❌ Leave logging enabled trong production
- ❌ Log sensitive data (passwords, keys)
- ❌ Log trong loops (performance)
- ❌ Over-log trivial operations

---

## 🔍 Log Analysis

### Find Issues

```bash
# Errors only
grep "\[ERROR\]" wp-content/debug.log | grep "JANKX_WOO"

# Warnings only
grep "\[WARNING\]" wp-content/debug.log | grep "JANKX_WOO"

# Specific component
grep "LayoutManager:" wp-content/debug.log

# Performance issues (duration > 100ms)
grep "duration_ms" wp-content/debug.log | grep -E "[1-9][0-9]{2,}"
```

### Count Events

```bash
# How many layouts registered?
grep "Layout registered successfully" wp-content/debug.log | wc -l

# How many CSS compilations?
grep "SCSS compiled successfully" wp-content/debug.log | wc -l
```

---

## 📈 Performance Monitoring

Logs include performance metrics:

```json
{
    "duration_ms": 15.23,
    "memory_mb": 12.5,
    "size_bytes": 5432
}
```

**Monitor:**
- Bootstrap duration
- CSS compilation time
- Memory usage
- File sizes

---

## 🚀 Production Checklist

Before going live:

- [ ] Set `JANKX_WOO_LAYOUT_DEBUG = false`
- [ ] Remove `?jankx_woo_debug=1` from URLs
- [ ] Clear log files
- [ ] Remove `?jankx_woo_dump_logs=1`
- [ ] Verify no sensitive data in logs
- [ ] Consider removing Logger calls (optional)

---

## 📞 Troubleshooting

### Logs không xuất hiện

```php
// Check debug enabled
var_dump(
    defined('JANKX_WOO_LAYOUT_DEBUG'),
    JANKX_WOO_LAYOUT_DEBUG,
    \Jankx\WooCommerce\Helpers\Logger::isDebugEnabled()
);
```

### Log file permission issues

```bash
# Check permissions
ls -la wp-content/debug-jankx-woo.log

# Fix permissions
chmod 644 wp-content/debug-jankx-woo.log
```

### Too many logs

```php
// Clear old logs
Logger::clearLogs();

// Or delete log file
unlink(WP_CONTENT_DIR . '/debug-jankx-woo.log');
```

---

**Logging system giúp debug và monitor package một cách hiệu quả! 🔍**

**Remember**: Disable logging trong production để tối ưu performance! ⚡

