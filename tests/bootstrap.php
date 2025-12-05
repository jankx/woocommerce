<?php
/**
 * PHPUnit Bootstrap File
 */

// Define testing constants
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/wordpress/');
}

if (!defined('WP_DEBUG')) {
    define('WP_DEBUG', true);
}

if (!defined('DAY_IN_SECONDS')) {
    define('DAY_IN_SECONDS', 86400);
}

// Load WordPressMocks class FIRST
require_once __DIR__ . '/Helpers/WordPressMocks.php';

// Load ALL WordPress functions (CRITICAL: Before autoloader)
require_once __DIR__ . '/Helpers/functions.php';

// Initialize WordPress Mock storage
\Jankx\WooCommerce\Tests\Helpers\WordPressMocks::initStorage();

// NOW load Composer autoloader
$autoloader = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
}

// Load TestCase
require_once __DIR__ . '/Helpers/TestCase.php';

echo "\n";
echo "===========================================\n";
echo "Jankx WooCommerce Layout System Test Suite\n";
echo "===========================================\n";
echo "\n";

