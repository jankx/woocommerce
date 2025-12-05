<?php

/**
 * Bootstrap file for Jankx WooCommerce Package
 * 
 * This file should be included in the theme's autoloader
 * to ensure WooCommerce integration is available
 *
 * @package Jankx\WooCommerce
 */

// Always load WooCommerce integration file
// It will check if WooCommerce is active internally
$woocommerce_file = __DIR__ . '/includes/WooCommerce.php';
if (file_exists($woocommerce_file)) {
    require_once $woocommerce_file;
} else {
    error_log('WooCommerce bootstrap: File not found: ' . $woocommerce_file);
}

// Initialize Layout System
\Jankx\WooCommerce\LayoutSystem\LayoutBootstrap::getInstance();
