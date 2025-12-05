<?php

namespace Jankx\WooCommerce\Contracts;

/**
 * Interface CssManagerInterface
 * 
 * Quản lý CSS compilation và injection
 */
interface CssManagerInterface
{
    /**
     * Compile SCSS sang CSS
     *
     * @param string $scssPath Đường dẫn file SCSS
     * @return string Compiled CSS
     */
    public function compile(string $scssPath): string;

    /**
     * Inject CSS vào HTML (inline)
     *
     * @param string $css CSS content
     * @param string $layoutId Layout ID để tracking
     * @return void
     */
    public function inject(string $css, string $layoutId): void;

    /**
     * Kiểm tra CSS đã được inject chưa
     *
     * @param string $layoutId
     * @return bool
     */
    public function isInjected(string $layoutId): bool;

    /**
     * Cache compiled CSS
     *
     * @param string $key Cache key
     * @param string $css CSS content
     * @param int $expiration Cache expiration in seconds
     * @return bool
     */
    public function cache(string $key, string $css, int $expiration = 3600): bool;

    /**
     * Get cached CSS
     *
     * @param string $key Cache key
     * @return string|null
     */
    public function getCached(string $key): ?string;

    /**
     * Clear cache cho layout
     *
     * @param string $layoutId
     * @return bool
     */
    public function clearCache(string $layoutId): bool;
}

