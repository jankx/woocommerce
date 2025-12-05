<?php

namespace Jankx\WooCommerce\Helpers;

/**
 * Class Logger
 * 
 * Logging system để track package loading và events
 * 
 * Enable/Disable via constant:
 * define('JANKX_WOO_LAYOUT_DEBUG', true);
 * 
 * Logs có prefix [JANKX_WOO] để dễ filter
 */
class Logger
{
    /**
     * @var bool Debug mode
     */
    private static $debugMode = null;

    /**
     * @var array Log entries
     */
    private static $logs = [];

    /**
     * @var string Log prefix
     */
    private const PREFIX = '[JANKX_WOO_LAYOUT]';

    /**
     * Check if debug mode enabled
     *
     * @return bool
     */
    public static function isDebugEnabled(): bool
    {
        if (self::$debugMode === null) {
            // Enable nếu:
            // 1. Constant JANKX_WOO_LAYOUT_DEBUG = true
            // 2. WP_DEBUG = true VÀ có query param ?jankx_woo_debug=1
            self::$debugMode = defined('JANKX_WOO_LAYOUT_DEBUG') && JANKX_WOO_LAYOUT_DEBUG;
            
            if (!self::$debugMode && defined('WP_DEBUG') && WP_DEBUG) {
                self::$debugMode = isset($_GET['jankx_woo_debug']) && $_GET['jankx_woo_debug'] == 1;
            }
        }

        return self::$debugMode;
    }

    /**
     * Log info message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function info(string $message, array $context = []): void
    {
        self::log('INFO', $message, $context);
    }

    /**
     * Log debug message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function debug(string $message, array $context = []): void
    {
        self::log('DEBUG', $message, $context);
    }

    /**
     * Log warning message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function warning(string $message, array $context = []): void
    {
        self::log('WARNING', $message, $context);
    }

    /**
     * Log error message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public static function error(string $message, array $context = []): void
    {
        self::log('ERROR', $message, $context);
    }

    /**
     * Log message
     *
     * @param string $level
     * @param string $message
     * @param array $context
     * @return void
     */
    private static function log(string $level, string $message, array $context = []): void
    {
        if (!self::isDebugEnabled()) {
            return;
        }

        $timestamp = current_time('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
        
        $logEntry = sprintf(
            '%s [%s] %s %s%s',
            $timestamp,
            $level,
            self::PREFIX,
            $message,
            $contextStr
        );

        // Store in memory
        self::$logs[] = [
            'timestamp' => $timestamp,
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];

        // Log to error_log
        if (function_exists('error_log')) {
            error_log($logEntry);
        }

        // Log to custom file nếu định nghĩa
        if (defined('JANKX_WOO_LAYOUT_LOG_FILE')) {
            self::logToFile(JANKX_WOO_LAYOUT_LOG_FILE, $logEntry);
        }
    }

    /**
     * Log to file
     *
     * @param string $file
     * @param string $entry
     * @return void
     */
    private static function logToFile(string $file, string $entry): void
    {
        $dir = dirname($file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        @file_put_contents($file, $entry . PHP_EOL, FILE_APPEND);
    }

    /**
     * Get all logs
     *
     * @return array
     */
    public static function getLogs(): array
    {
        return self::$logs;
    }

    /**
     * Get logs by level
     *
     * @param string $level
     * @return array
     */
    public static function getLogsByLevel(string $level): array
    {
        return array_filter(self::$logs, function ($log) use ($level) {
            return $log['level'] === $level;
        });
    }

    /**
     * Clear logs
     *
     * @return void
     */
    public static function clearLogs(): void
    {
        self::$logs = [];
    }

    /**
     * Dump logs (for debugging)
     *
     * @return void
     */
    public static function dump(): void
    {
        if (!self::isDebugEnabled()) {
            return;
        }

        echo "\n" . str_repeat('=', 80) . "\n";
        echo "JANKX WOOCOMMERCE LAYOUT DEBUG LOGS\n";
        echo str_repeat('=', 80) . "\n\n";

        foreach (self::$logs as $log) {
            printf(
                "[%s] %s: %s\n",
                $log['timestamp'],
                $log['level'],
                $log['message']
            );
            
            if (!empty($log['context'])) {
                echo "  Context: " . json_encode($log['context'], JSON_PRETTY_PRINT) . "\n";
            }
        }

        echo "\n" . str_repeat('=', 80) . "\n";
        echo "Total Logs: " . count(self::$logs) . "\n";
        echo str_repeat('=', 80) . "\n\n";
    }

    /**
     * Add log entry to admin bar (nếu có quyền)
     *
     * @return void
     */
    public static function addToAdminBar(): void
    {
        if (!self::isDebugEnabled()) {
            return;
        }

        add_action('admin_bar_menu', function ($wp_admin_bar) {
            if (!current_user_can('manage_options')) {
                return;
            }

            $logCount = count(self::$logs);
            
            $wp_admin_bar->add_node([
                'id' => 'jankx-woo-logs',
                'title' => sprintf('Jankx WooCommerce Logs (%d)', $logCount),
                'href' => '#',
            ]);

            // Add log entries as sub-items
            foreach (array_slice(self::$logs, -10) as $index => $log) {
                $wp_admin_bar->add_node([
                    'parent' => 'jankx-woo-logs',
                    'id' => 'jankx-woo-log-' . $index,
                    'title' => sprintf('[%s] %s', $log['level'], substr($log['message'], 0, 50)),
                ]);
            }
        }, 999);
    }
}

