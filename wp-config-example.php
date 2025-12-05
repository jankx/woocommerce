<?php
/**
 * Example wp-config.php additions for Jankx WooCommerce Layout System
 * 
 * Copy các defines này vào wp-config.php của bạn để enable debug mode
 */

// ============================================
// JANKX WOOCOMMERCE LAYOUT DEBUG
// ============================================

/**
 * Enable debug logging
 * 
 * Logs xuất hiện trong:
 * - wp-content/debug.log (nếu WP_DEBUG_LOG = true)
 * - Custom log file (nếu set JANKX_WOO_LAYOUT_LOG_FILE)
 * - Admin Bar (khi logged in as admin)
 */
define('JANKX_WOO_LAYOUT_DEBUG', true);

/**
 * Custom log file (optional)
 * 
 * Nếu không set, logs sẽ đi vào wp-content/debug.log
 */
define('JANKX_WOO_LAYOUT_LOG_FILE', WP_CONTENT_DIR . '/logs/jankx-woo-layout.log');

/**
 * WordPress Debug (recommended để bật khi debug)
 */
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false); // Không show errors trên screen

// ============================================
// VIEW LOGS
// ============================================

/**
 * Xem logs qua:
 * 
 * 1. Admin Bar:
 *    - Login as admin
 *    - Check "Jankx WooCommerce Logs (X)" trong admin bar
 * 
 * 2. Debug.log:
 *    tail -f wp-content/debug.log | grep "JANKX_WOO"
 * 
 * 3. Custom log file:
 *    tail -f wp-content/logs/jankx-woo-layout.log
 * 
 * 4. Dump to screen:
 *    https://your-site.com/?jankx_woo_dump_logs=1
 *    (Logs xuất hiện trong footer)
 * 
 * 5. URL debug:
 *    https://your-site.com/?jankx_woo_debug=1
 *    (Enable logging cho request đó only)
 */

// ============================================
// DISABLE DEBUG (PRODUCTION)
// ============================================

/**
 * Trong production, set = false:
 */
// define('JANKX_WOO_LAYOUT_DEBUG', false);

/**
 * Hoặc comment out:
 */
// // define('JANKX_WOO_LAYOUT_DEBUG', true);

// ============================================
// GREP COMMANDS
// ============================================

/**
 * Useful grep commands:
 * 
 * # All logs
 * grep "JANKX_WOO" wp-content/debug.log
 * 
 * # Errors only
 * grep "\[ERROR\].*JANKX_WOO" wp-content/debug.log
 * 
 * # Warnings only
 * grep "\[WARNING\].*JANKX_WOO" wp-content/debug.log
 * 
 * # Bootstrap events
 * grep "Bootstrap:" wp-content/debug.log
 * 
 * # Layout registration
 * grep "LayoutManager: Registering" wp-content/debug.log
 * 
 * # CSS compilation
 * grep "CssManager: Compiling" wp-content/debug.log
 * 
 * # Performance issues (> 100ms)
 * grep "duration_ms" wp-content/debug.log | grep -E "[1-9][0-9]{2,}"
 * 
 * # Count events
 * grep "Layout registered successfully" wp-content/debug.log | wc -l
 * 
 * # Last 20 logs
 * grep "JANKX_WOO" wp-content/debug.log | tail -20
 * 
 * # Watch logs in real-time
 * tail -f wp-content/debug.log | grep --line-buffered "JANKX_WOO"
 */

