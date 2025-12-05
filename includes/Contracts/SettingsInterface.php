<?php

namespace Jankx\WooCommerce\Contracts;

/**
 * Interface SettingsInterface
 * 
 * Quản lý settings cho layouts
 */
interface SettingsInterface
{
    /**
     * Lấy setting value
     *
     * @param string $key Setting key
     * @param mixed $default Default value
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Set setting value
     *
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool
     */
    public function set(string $key, $value): bool;

    /**
     * Lấy tất cả settings
     *
     * @return array
     */
    public function all(): array;

    /**
     * Generate CSS từ settings
     *
     * @param string $layoutId
     * @return string CSS content
     */
    public function generateCss(string $layoutId): string;

    /**
     * Validate settings
     *
     * @param array $settings
     * @return bool
     */
    public function validate(array $settings): bool;
}

