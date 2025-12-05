<?php

namespace Jankx\WooCommerce\Abstracts;

use Jankx\WooCommerce\Contracts\CssManagerInterface;

/**
 * Abstract Class AbstractCssManager
 * 
 * Base implementation cho CSS Manager
 */
abstract class AbstractCssManager implements CssManagerInterface
{
    /**
     * @var array Tracking injected CSS
     */
    protected $injectedCss = [];

    /**
     * @var string Cache prefix
     */
    protected $cachePrefix = 'jankx_woo_css_';

    /**
     * @var bool Development mode (skip cache)
     */
    protected $devMode = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->devMode = defined('WP_DEBUG') && WP_DEBUG;
        $this->init();
    }

    /**
     * Initialize
     *
     * @return void
     */
    protected function init(): void
    {
        // Hook để inject CSS vào wp_head
        add_action('wp_head', [$this, 'outputInjectedCss'], 999);
    }

    /**
     * {@inheritDoc}
     */
    public function inject(string $css, string $layoutId): void
    {
        if ($this->isInjected($layoutId)) {
            return;
        }

        $this->injectedCss[$layoutId] = $css;
    }

    /**
     * {@inheritDoc}
     */
    public function isInjected(string $layoutId): bool
    {
        return isset($this->injectedCss[$layoutId]);
    }

    /**
     * Output injected CSS vào HTML
     *
     * @return void
     */
    public function outputInjectedCss(): void
    {
        if (empty($this->injectedCss)) {
            return;
        }

        // Minify và output CSS
        $css = $this->minifyCss(implode("\n", $this->injectedCss));
        
        echo sprintf(
            "\n<!-- Jankx WooCommerce Inline CSS -->\n<style id=\"jankx-woo-inline-css\" type=\"text/css\">\n%s\n</style>\n",
            $css
        );
    }

    /**
     * {@inheritDoc}
     */
    public function cache(string $key, string $css, int $expiration = 3600): bool
    {
        if ($this->devMode) {
            return false;
        }

        $cacheKey = $this->cachePrefix . $key;
        return set_transient($cacheKey, $css, $expiration);
    }

    /**
     * {@inheritDoc}
     */
    public function getCached(string $key): ?string
    {
        if ($this->devMode) {
            return null;
        }

        $cacheKey = $this->cachePrefix . $key;
        $cached = get_transient($cacheKey);

        return $cached !== false ? $cached : null;
    }

    /**
     * {@inheritDoc}
     */
    public function clearCache(string $layoutId): bool
    {
        $cacheKey = $this->cachePrefix . 'layout_common_css_' . $layoutId;
        return delete_transient($cacheKey);
    }

    /**
     * Minify CSS
     *
     * @param string $css
     * @return string
     */
    protected function minifyCss(string $css): string
    {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Remove whitespace
        $css = str_replace(["\r\n", "\r", "\n", "\t"], '', $css);
        $css = preg_replace('/\s+/', ' ', $css);
        $css = preg_replace('/\s*([:;{}])\s*/', '$1', $css);
        
        return trim($css);
    }

    /**
     * {@inheritDoc}
     */
    abstract public function compile(string $scssPath): string;
}

