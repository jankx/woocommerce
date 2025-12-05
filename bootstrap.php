<?php

/**
 * Bootstrap file for Jankx WooCommerce Package
 * 
 * This file should be included in the theme's autoloader
 * to ensure WooCommerce integration is available
 *
 * @package Jankx\WooCommerce
 * 
 * DEBUG MODE:
 * define('JANKX_WOO_LAYOUT_DEBUG', true); trong wp-config.php
 * hoặc thêm ?jankx_woo_debug=1 vào URL khi WP_DEBUG = true
 * 
 * VIEW LOGS:
 * - Admin Bar (khi logged in)
 * - wp-content/debug.log
 * - URL với ?jankx_woo_dump_logs=1
 */

use Jankx\WooCommerce\Helpers\Logger;

// [LOG] Bootstrap start
if (class_exists('\Jankx\WooCommerce\Helpers\Logger')) {
    Logger::info('Bootstrap: Starting Jankx WooCommerce package initialization', [
        'file' => __FILE__,
        'time' => microtime(true),
        'php_version' => PHP_VERSION,
    ]);
}

// Always load WooCommerce integration file
// It will check if WooCommerce is active internally
$woocommerce_file = __DIR__ . '/includes/WooCommerce.php';
if (file_exists($woocommerce_file)) {
    Logger::debug('Bootstrap: Loading WooCommerce integration file', [
        'file' => $woocommerce_file,
    ]);
    require_once $woocommerce_file;
    Logger::info('Bootstrap: WooCommerce integration file loaded successfully');
} else {
    Logger::error('Bootstrap: WooCommerce integration file not found', [
        'expected_path' => $woocommerce_file,
    ]);
    error_log('WooCommerce bootstrap: File not found: ' . $woocommerce_file);
}

// Initialize Layout System
if (class_exists('\Jankx\WooCommerce\LayoutSystem\LayoutBootstrap')) {
    Logger::debug('Bootstrap: LayoutBootstrap class found, initializing');
    
    $startTime = microtime(true);
    $bootstrap = \Jankx\WooCommerce\LayoutSystem\LayoutBootstrap::getInstance();
    $duration = microtime(true) - $startTime;
    
    Logger::info('Bootstrap: LayoutBootstrap initialized', [
        'duration_ms' => round($duration * 1000, 2),
        'memory_mb' => round(memory_get_usage() / 1024 / 1024, 2),
    ]);
    
    // Add logs to admin bar
    Logger::addToAdminBar();
    
    // Dump logs in footer (only if debug query param)
    add_action('wp_footer', function() {
        if (isset($_GET['jankx_woo_dump_logs'])) {
            Logger::dump();
        }
    }, 999);
} else {
    Logger::error('Bootstrap: LayoutBootstrap class not found', [
        'expected_class' => '\Jankx\WooCommerce\LayoutSystem\LayoutBootstrap',
    ]);
}

Logger::info('Bootstrap: Package bootstrap completed', [
    'total_memory_mb' => round(memory_get_usage() / 1024 / 1024, 2),
    'peak_memory_mb' => round(memory_get_peak_usage() / 1024 / 1024, 2),
]);
