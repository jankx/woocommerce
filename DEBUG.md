# Debug Mode - Quick Reference

## 🔥 Enable Debug Mode

### wp-config.php
```php
// Add này vào wp-config.php
define('JANKX_WOO_LAYOUT_DEBUG', true);

// Optional: Custom log file
define('JANKX_WOO_LAYOUT_LOG_FILE', WP_CONTENT_DIR . '/jankx-woo-debug.log');
```

### URL Parameter
```
https://your-site.com/?jankx_woo_debug=1
```
*(Chỉ hoạt động khi WP_DEBUG = true)*

---

## 👀 View Logs

### 1. WordPress debug.log
```bash
tail -f wp-content/debug.log | grep "JANKX_WOO"
```

### 2. Custom log file
```bash
tail -f wp-content/jankx-woo-debug.log
```

### 3. Admin Bar
- Login as admin
- Check Admin Bar → "Jankx WooCommerce Logs (X)"

### 4. Dump to screen
```
https://your-site.com/?jankx_woo_dump_logs=1
```

---

## 📊 What's Logged

- ✅ Package bootstrap
- ✅ Service provider registration
- ✅ Layout registration
- ✅ CSS compilation
- ✅ Configuration loading
- ✅ Performance metrics
- ✅ Errors và warnings

---

## 🧹 Disable Debug

```php
// wp-config.php
define('JANKX_WOO_LAYOUT_DEBUG', false);
```

---

## 🔍 Quick Commands

```bash
# Errors only
grep "\[ERROR\].*JANKX_WOO" wp-content/debug.log

# Last 20 logs
grep "JANKX_WOO" wp-content/debug.log | tail -20

# Count layouts registered
grep "Layout registered successfully" wp-content/debug.log | wc -l

# Performance issues
grep "duration_ms" wp-content/debug.log | grep -E "[1-9][0-9]{2,}"
```

---

**Quick debug = Happy developer! 🚀**

See [LOGGING.md](LOGGING.md) for complete guide.

