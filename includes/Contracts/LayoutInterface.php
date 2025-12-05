<?php

namespace Jankx\WooCommerce\Contracts;

/**
 * Interface LayoutInterface
 * 
 * Định nghĩa contract cơ bản cho tất cả các layout trong hệ thống
 */
interface LayoutInterface
{
    /**
     * Lấy ID duy nhất của layout
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Lấy tên hiển thị của layout
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Lấy loại layout (product-detail, product-loop, cart, etc.)
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Render layout với data
     *
     * @param array $data Dữ liệu để render
     * @return string HTML output
     */
    public function render(array $data = []): string;

    /**
     * Lấy common CSS cho layout này
     *
     * @return string CSS content
     */
    public function getCommonCss(): string;

    /**
     * Lấy dynamic CSS dựa trên settings
     *
     * @param array $settings User settings
     * @return string CSS content
     */
    public function getDynamicCss(array $settings = []): string;

    /**
     * Lấy đường dẫn đến SCSS file
     *
     * @return string
     */
    public function getScssPath(): string;

    /**
     * Kiểm tra layout có support settings này không
     *
     * @param string $settingKey
     * @return bool
     */
    public function supportsetting(string $settingKey): bool;

    /**
     * Lấy priority để sort layouts
     *
     * @return int
     */
    public function getPriority(): int;
}

