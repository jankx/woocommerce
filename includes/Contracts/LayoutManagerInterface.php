<?php

namespace Jankx\WooCommerce\Contracts;

/**
 * Interface LayoutManagerInterface
 * 
 * Quản lý việc đăng ký và sử dụng layouts
 */
interface LayoutManagerInterface
{
    /**
     * Đăng ký một layout mới
     *
     * @param LayoutInterface $layout
     * @return bool
     */
    public function register(LayoutInterface $layout): bool;

    /**
     * Hủy đăng ký layout
     *
     * @param string $layoutId
     * @return bool
     */
    public function unregister(string $layoutId): bool;

    /**
     * Lấy layout theo ID
     *
     * @param string $layoutId
     * @return LayoutInterface|null
     */
    public function get(string $layoutId): ?LayoutInterface;

    /**
     * Lấy tất cả layouts theo type
     *
     * @param string $type
     * @return array<LayoutInterface>
     */
    public function getByType(string $type): array;

    /**
     * Lấy default layout cho type
     *
     * @param string $type
     * @return LayoutInterface|null
     */
    public function getDefault(string $type): ?LayoutInterface;

    /**
     * Set default layout cho type
     *
     * @param string $type
     * @param string $layoutId
     * @return bool
     */
    public function setDefault(string $type, string $layoutId): bool;

    /**
     * Kiểm tra layout có tồn tại không
     *
     * @param string $layoutId
     * @return bool
     */
    public function has(string $layoutId): bool;

    /**
     * Lấy tất cả layouts
     *
     * @return array<LayoutInterface>
     */
    public function all(): array;
}

