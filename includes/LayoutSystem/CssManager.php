<?php

namespace Jankx\WooCommerce\LayoutSystem;

use Jankx\WooCommerce\Helpers\Logger;
use Jankx\WooCommerce\Abstracts\AbstractCssManager;
use ScssPhp\ScssPhp\Compiler;

/**
 * Class CssManager
 * 
 * Quản lý compilation và injection của CSS/SCSS
 * Singleton pattern
 */
class CssManager extends AbstractCssManager
{
    /**
     * @var CssManager Singleton instance
     */
    private static $instance = null;

    /**
     * @var Compiler SCSS compiler
     */
    private $compiler;

    /**
     * Get singleton instance
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * {@inheritDoc}
     */
    protected function init(): void
    {
        parent::init();
        $this->initCompiler();
    }

    /**
     * Initialize SCSS compiler
     *
     * @return void
     */
    private function initCompiler(): void
    {
        if (!class_exists('ScssPhp\ScssPhp\Compiler')) {
            // Fallback nếu không có ScssPhp
            return;
        }

        $this->compiler = new Compiler();
        
        // Set import paths
        $importPaths = [
            dirname(dirname(__DIR__)) . '/assets/scss',
            dirname(dirname(__DIR__)) . '/assets/scss/layouts',
        ];
        
        $importPaths = apply_filters('jankx_woocommerce_scss_import_paths', $importPaths);
        $this->compiler->setImportPaths($importPaths);

        // Set output format
        if ($this->devMode) {
            $this->compiler->setOutputStyle(\ScssPhp\ScssPhp\OutputStyle::EXPANDED);
        } else {
            $this->compiler->setOutputStyle(\ScssPhp\ScssPhp\OutputStyle::COMPRESSED);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function compile(string $scssPath): string
    {
        \Jankx\WooCommerce\Helpers\Logger::debug('CssManager: Compiling SCSS file', [
            'file' => $scssPath,
        ]);

        if (!file_exists($scssPath)) {
            \Jankx\WooCommerce\Helpers\Logger::warning('CssManager: SCSS file not found', [
                'file' => $scssPath,
            ]);
            return '';
        }

        // Check cache first
        $cacheKey = 'compiled_' . md5($scssPath . filemtime($scssPath));
        $cached = $this->getCached($cacheKey);

        if ($cached !== null) {
            \Jankx\WooCommerce\Helpers\Logger::debug('CssManager: Using cached CSS', [
                'cache_key' => $cacheKey,
                'size_bytes' => strlen($cached),
            ]);
            return $cached;
        }

        \Jankx\WooCommerce\Helpers\Logger::debug('CssManager: Cache miss, compiling from source');

        $css = '';

        try {
            if ($this->compiler !== null) {
                // Compile với ScssPhp
                $scssContent = file_get_contents($scssPath);
                $css = $this->compiler->compileString($scssContent)->getCss();
            } else {
                // Fallback: đọc trực tiếp nếu là CSS hoặc có compiled version
                $cssPath = str_replace('.scss', '.css', $scssPath);
                
                if (file_exists($cssPath)) {
                    $css = file_get_contents($cssPath);
                } else {
                    // Basic SCSS processing (không hoàn hảo nhưng better than nothing)
                    $css = file_get_contents($scssPath);
                    $css = $this->basicScssProcess($css);
                }
            }

            // Post-process CSS
            $css = $this->postProcessCss($css);

            // Cache compiled CSS
            $this->cache($cacheKey, $css);
            
            \Jankx\WooCommerce\Helpers\Logger::info('CssManager: SCSS compiled successfully', [
                'file' => basename($scssPath),
                'size_bytes' => strlen($css),
                'cached' => true,
            ]);

        } catch (\Exception $e) {
            \Jankx\WooCommerce\Helpers\Logger::error('CssManager: SCSS compilation failed', [
                'file' => $scssPath,
                'error' => $e->getMessage(),
            ]);
            
            if ($this->devMode) {
                $css = sprintf('/* SCSS Compilation Error: %s */', esc_html($e->getMessage()));
            }
        }

        return $css;
    }

    /**
     * Basic SCSS processing (fallback)
     *
     * @param string $scss
     * @return string
     */
    private function basicScssProcess(string $scss): string
    {
        // Remove SCSS comments
        $scss = preg_replace('!//.+!', '', $scss);
        
        // Remove variables declarations (simple approach)
        $scss = preg_replace('/\$[\w-]+:\s*[^;]+;/', '', $scss);
        
        // Remove @import statements
        $scss = preg_replace('/@import\s+[^;]+;/', '', $scss);
        
        // Remove @mixin definitions
        $scss = preg_replace('/@mixin[\s\S]+?\{[\s\S]+?\}/', '', $scss);
        
        // Remove @include statements
        $scss = preg_replace('/@include\s+[^;]+;/', '', $scss);
        
        return $scss;
    }

    /**
     * Post-process compiled CSS
     *
     * @param string $css
     * @return string
     */
    private function postProcessCss(string $css): string
    {
        // Allow filters để modify CSS
        $css = apply_filters('jankx_woocommerce_compiled_css', $css);

        // Autoprefixer nếu cần (có thể integrate library)
        // For now, just return as-is

        return $css;
    }

    /**
     * Compile inline CSS từ string
     *
     * @param string $scssString SCSS string
     * @return string Compiled CSS
     */
    public function compileString(string $scssString): string
    {
        if ($this->compiler === null) {
            return $scssString;
        }

        try {
            $result = $this->compiler->compileString($scssString);
            return $result->getCss();
        } catch (\Exception $e) {
            if ($this->devMode) {
                return sprintf('/* SCSS Error: %s */', esc_html($e->getMessage()));
            }
            return '';
        }
    }

    /**
     * Inject layout CSS (common + dynamic)
     *
     * @param \Jankx\WooCommerce\Contracts\LayoutInterface $layout
     * @param array $settings User settings
     * @return void
     */
    public function injectLayoutCss($layout, array $settings = []): void
    {
        $layoutId = $layout->getId();

        \Jankx\WooCommerce\Helpers\Logger::debug('CssManager: Injecting layout CSS', [
            'layout_id' => $layoutId,
            'settings_count' => count($settings),
        ]);

        // Đã inject rồi thì skip
        if ($this->isInjected($layoutId)) {
            \Jankx\WooCommerce\Helpers\Logger::debug('CssManager: CSS already injected, skipping', [
                'layout_id' => $layoutId,
            ]);
            return;
        }

        $startTime = microtime(true);

        // Get common CSS
        $commonCss = $layout->getCommonCss();

        // Get dynamic CSS
        $dynamicCss = $layout->getDynamicCss($settings);

        // Combine
        $css = $commonCss . "\n" . $dynamicCss;

        // Inject
        $this->inject($css, $layoutId);
        
        $duration = microtime(true) - $startTime;
        \Jankx\WooCommerce\Helpers\Logger::info('CssManager: Layout CSS injected', [
            'layout_id' => $layoutId,
            'common_css_size' => strlen($commonCss),
            'dynamic_css_size' => strlen($dynamicCss),
            'total_size' => strlen($css),
            'duration_ms' => round($duration * 1000, 2),
        ]);
    }

    /**
     * Get CSS stats (for debugging)
     *
     * @return array
     */
    public function getStats(): array
    {
        $totalSize = 0;
        foreach ($this->injectedCss as $css) {
            $totalSize += strlen($css);
        }

        return [
            'layouts_count' => count($this->injectedCss),
            'total_size' => $totalSize,
            'total_size_formatted' => size_format($totalSize),
            'layouts' => array_keys($this->injectedCss),
        ];
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}

