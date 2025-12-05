<?php

namespace Jankx\WooCommerce\LayoutSystem;

use Jankx\WooCommerce\Contracts\LayoutInterface;
use Jankx\WooCommerce\Contracts\LayoutManagerInterface;

/**
 * Class LayoutManager
 * 
 * Registry Pattern implementation để quản lý layouts
 * Singleton pattern để đảm bảo chỉ có 1 instance
 */
class LayoutManager implements LayoutManagerInterface
{
    /**
     * @var LayoutManager Singleton instance
     */
    private static $instance = null;

    /**
     * @var array<string, LayoutInterface> Registered layouts
     */
    private $layouts = [];

    /**
     * @var array<string, string> Default layouts per type
     */
    private $defaults = [];

    /**
     * @var array<string, array> Layouts grouped by type
     */
    private $layoutsByType = [];

    /**
     * Private constructor để enforce singleton
     */
    private function __construct()
    {
        $this->init();
    }

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
     * Initialize manager
     *
     * @return void
     */
    private function init(): void
    {
        // Hook để cho phép themes/plugins đăng ký layouts
        add_action('init', [$this, 'registerDefaultLayouts'], 5);
        add_action('init', [$this, 'allowExternalRegistration'], 10);
    }

    /**
     * Register default layouts from system
     *
     * @return void
     */
    public function registerDefaultLayouts(): void
    {
        // Sẽ được implement ở phần sau khi tạo concrete layouts
        do_action('jankx_woocommerce_register_default_layouts', $this);
    }

    /**
     * Allow external registration từ themes/plugins
     *
     * @return void
     */
    public function allowExternalRegistration(): void
    {
        do_action('jankx_woocommerce_register_layouts', $this);
    }

    /**
     * {@inheritDoc}
     */
    public function register(LayoutInterface $layout): bool
    {
        $layoutId = $layout->getId();
        $layoutType = $layout->getType();

        // Kiểm tra duplicate
        if ($this->has($layoutId)) {
            // Allow override với filter
            if (!apply_filters('jankx_woocommerce_allow_layout_override', false, $layoutId, $layout)) {
                return false;
            }
        }

        // Register layout
        $this->layouts[$layoutId] = $layout;

        // Group by type
        if (!isset($this->layoutsByType[$layoutType])) {
            $this->layoutsByType[$layoutType] = [];
        }
        $this->layoutsByType[$layoutType][$layoutId] = $layout;

        // Set as default nếu là layout đầu tiên của type này
        if (!isset($this->defaults[$layoutType])) {
            $this->setDefault($layoutType, $layoutId);
        }

        do_action('jankx_woocommerce_layout_registered', $layout, $this);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function unregister(string $layoutId): bool
    {
        if (!$this->has($layoutId)) {
            return false;
        }

        $layout = $this->layouts[$layoutId];
        $layoutType = $layout->getType();

        // Remove from main registry
        unset($this->layouts[$layoutId]);

        // Remove from type group
        if (isset($this->layoutsByType[$layoutType][$layoutId])) {
            unset($this->layoutsByType[$layoutType][$layoutId]);
        }

        // Clear default nếu layout này là default
        if (isset($this->defaults[$layoutType]) && $this->defaults[$layoutType] === $layoutId) {
            unset($this->defaults[$layoutType]);
            
            // Set layout khác làm default
            if (!empty($this->layoutsByType[$layoutType])) {
                $firstLayout = reset($this->layoutsByType[$layoutType]);
                $this->setDefault($layoutType, $firstLayout->getId());
            }
        }

        do_action('jankx_woocommerce_layout_unregistered', $layoutId, $layoutType, $this);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $layoutId): ?LayoutInterface
    {
        return $this->layouts[$layoutId] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function getByType(string $type): array
    {
        if (!isset($this->layoutsByType[$type])) {
            return [];
        }

        // Sort by priority
        $layouts = $this->layoutsByType[$type];
        uasort($layouts, function ($a, $b) {
            return $a->getPriority() <=> $b->getPriority();
        });

        return $layouts;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefault(string $type): ?LayoutInterface
    {
        $defaultId = $this->defaults[$type] ?? null;
        
        if ($defaultId === null) {
            return null;
        }

        // Allow filter để override default
        $defaultId = apply_filters('jankx_woocommerce_default_layout_id', $defaultId, $type);

        return $this->get($defaultId);
    }

    /**
     * {@inheritDoc}
     */
    public function setDefault(string $type, string $layoutId): bool
    {
        // Kiểm tra layout có tồn tại không
        $layout = $this->get($layoutId);
        
        if ($layout === null) {
            return false;
        }

        // Kiểm tra type có match không
        if ($layout->getType() !== $type) {
            return false;
        }

        $this->defaults[$type] = $layoutId;

        // Save to options để persistent
        $this->saveDefaults();

        do_action('jankx_woocommerce_default_layout_changed', $type, $layoutId, $this);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $layoutId): bool
    {
        return isset($this->layouts[$layoutId]);
    }

    /**
     * {@inheritDoc}
     */
    public function all(): array
    {
        return $this->layouts;
    }

    /**
     * Get all layout types
     *
     * @return array
     */
    public function getTypes(): array
    {
        return array_keys($this->layoutsByType);
    }

    /**
     * Get layout count
     *
     * @param string|null $type Specific type or null for all
     * @return int
     */
    public function count(?string $type = null): int
    {
        if ($type === null) {
            return count($this->layouts);
        }

        return count($this->layoutsByType[$type] ?? []);
    }

    /**
     * Save default layouts to options
     *
     * @return bool
     */
    private function saveDefaults(): bool
    {
        return update_option('jankx_woocommerce_default_layouts', $this->defaults);
    }

    /**
     * Load default layouts from options
     *
     * @return void
     */
    private function loadDefaults(): void
    {
        $saved = get_option('jankx_woocommerce_default_layouts', []);
        
        if (is_array($saved)) {
            $this->defaults = $saved;
        }
    }

    /**
     * Clear all layouts (useful for testing)
     *
     * @return void
     */
    public function clearAll(): void
    {
        $this->layouts = [];
        $this->layoutsByType = [];
        $this->defaults = [];
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

