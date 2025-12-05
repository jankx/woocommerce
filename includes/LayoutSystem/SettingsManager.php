<?php

namespace Jankx\WooCommerce\LayoutSystem;

use Jankx\WooCommerce\Contracts\SettingsInterface;

/**
 * Class SettingsManager
 * 
 * Quản lý settings cho layouts
 * Singleton pattern
 */
class SettingsManager implements SettingsInterface
{
    /**
     * @var SettingsManager Singleton instance
     */
    private static $instance = null;

    /**
     * @var array Settings storage
     */
    private $settings = [];

    /**
     * @var string Option key
     */
    private $optionKey = 'jankx_woocommerce_layout_settings';

    /**
     * @var array CSS generators per layout type
     */
    private $cssGenerators = [];

    /**
     * Private constructor
     */
    private function __construct()
    {
        $this->loadSettings();
        $this->registerDefaultCssGenerators();
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
     * Load settings from database
     *
     * @return void
     */
    private function loadSettings(): void
    {
        $saved = get_option($this->optionKey, []);
        
        if (is_array($saved)) {
            $this->settings = $saved;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $key, $default = null)
    {
        // Support dot notation: layout_id.setting_key
        if (strpos($key, '.') !== false) {
            $parts = explode('.', $key, 2);
            $layoutId = $parts[0];
            $settingKey = $parts[1];

            return $this->settings[$layoutId][$settingKey] ?? $default;
        }

        return $this->settings[$key] ?? $default;
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $key, $value): bool
    {
        // Support dot notation
        if (strpos($key, '.') !== false) {
            $parts = explode('.', $key, 2);
            $layoutId = $parts[0];
            $settingKey = $parts[1];

            if (!isset($this->settings[$layoutId])) {
                $this->settings[$layoutId] = [];
            }

            $this->settings[$layoutId][$settingKey] = $value;
        } else {
            $this->settings[$key] = $value;
        }

        return $this->saveSettings();
    }

    /**
     * Get settings for specific layout
     *
     * @param string $layoutId
     * @return array
     */
    public function getLayoutSettings(string $layoutId): array
    {
        return $this->settings[$layoutId] ?? [];
    }

    /**
     * Set multiple settings for layout
     *
     * @param string $layoutId
     * @param array $settings
     * @return bool
     */
    public function setLayoutSettings(string $layoutId, array $settings): bool
    {
        if (!$this->validate($settings)) {
            return false;
        }

        $this->settings[$layoutId] = array_merge(
            $this->settings[$layoutId] ?? [],
            $settings
        );

        return $this->saveSettings();
    }

    /**
     * {@inheritDoc}
     */
    public function all(): array
    {
        return $this->settings;
    }

    /**
     * {@inheritDoc}
     */
    public function generateCss(string $layoutId): string
    {
        $layoutSettings = $this->getLayoutSettings($layoutId);
        
        if (empty($layoutSettings)) {
            return '';
        }

        // Get layout to determine type
        $layout = LayoutManager::getInstance()->get($layoutId);
        
        if ($layout === null) {
            return '';
        }

        $layoutType = $layout->getType();

        // Check if có custom CSS generator
        if (isset($this->cssGenerators[$layoutType])) {
            $generator = $this->cssGenerators[$layoutType];
            return call_user_func($generator, $layoutId, $layoutSettings);
        }

        // Use layout's own generator
        return $layout->getDynamicCss($layoutSettings);
    }

    /**
     * Register CSS generator cho layout type
     *
     * @param string $layoutType
     * @param callable $generator
     * @return self
     */
    public function registerCssGenerator(string $layoutType, callable $generator): self
    {
        $this->cssGenerators[$layoutType] = $generator;
        return $this;
    }

    /**
     * Register default CSS generators
     *
     * @return void
     */
    private function registerDefaultCssGenerators(): void
    {
        // Common CSS generator cho các settings phổ biến
        $commonGenerator = function ($layoutId, $settings) {
            $css = '';

            // Colors
            if (isset($settings['primary_color'])) {
                $css .= sprintf(
                    '.layout-%s .button, .layout-%s .btn-primary { background-color: %s; }',
                    $layoutId,
                    $layoutId,
                    esc_attr($settings['primary_color'])
                );
            }

            if (isset($settings['text_color'])) {
                $css .= sprintf(
                    '.layout-%s { color: %s; }',
                    $layoutId,
                    esc_attr($settings['text_color'])
                );
            }

            // Spacing
            if (isset($settings['padding'])) {
                $css .= sprintf(
                    '.layout-%s { padding: %dpx; }',
                    $layoutId,
                    intval($settings['padding'])
                );
            }

            if (isset($settings['margin'])) {
                $css .= sprintf(
                    '.layout-%s { margin: %dpx; }',
                    $layoutId,
                    intval($settings['margin'])
                );
            }

            // Border radius
            if (isset($settings['border_radius'])) {
                $css .= sprintf(
                    '.layout-%s .item, .layout-%s .card { border-radius: %dpx; }',
                    $layoutId,
                    $layoutId,
                    intval($settings['border_radius'])
                );
            }

            return $css;
        };

        // Register for all types by default
        $types = [
            'product-detail',
            'product-loop',
            'product-category-block',
            'product-gallery',
            'cart-form',
            'cart-page',
            'checkout-page',
            'quick-checkout',
        ];

        foreach ($types as $type) {
            $this->registerCssGenerator($type, $commonGenerator);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function validate(array $settings): bool
    {
        // Basic validation
        foreach ($settings as $key => $value) {
            // Validate color settings
            if (strpos($key, 'color') !== false) {
                if (!$this->isValidColor($value)) {
                    return false;
                }
            }

            // Validate numeric settings
            if (in_array($key, ['padding', 'margin', 'border_radius', 'width', 'height'])) {
                if (!is_numeric($value) || $value < 0) {
                    return false;
                }
            }
        }

        return apply_filters('jankx_woocommerce_validate_layout_settings', true, $settings);
    }

    /**
     * Validate color value
     *
     * @param mixed $color
     * @return bool
     */
    private function isValidColor($color): bool
    {
        if (!is_string($color)) {
            return false;
        }

        // Hex color
        if (preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color)) {
            return true;
        }

        // RGB/RGBA
        if (preg_match('/^rgba?\([\d\s,\.]+\)$/', $color)) {
            return true;
        }

        return false;
    }

    /**
     * Save settings to database
     *
     * @return bool
     */
    private function saveSettings(): bool
    {
        $result = update_option($this->optionKey, $this->settings);

        // Clear CSS cache khi settings thay đổi
        if ($result) {
            $this->clearCssCache();
        }

        return $result;
    }

    /**
     * Clear CSS cache for all layouts
     *
     * @return void
     */
    private function clearCssCache(): void
    {
        $cssManager = CssManager::getInstance();
        
        foreach (array_keys($this->settings) as $layoutId) {
            $cssManager->clearCache($layoutId);
        }
    }

    /**
     * Export settings
     *
     * @return string JSON
     */
    public function export(): string
    {
        return wp_json_encode($this->settings, JSON_PRETTY_PRINT);
    }

    /**
     * Import settings
     *
     * @param string $json
     * @return bool
     */
    public function import(string $json): bool
    {
        $data = json_decode($json, true);
        
        if (!is_array($data)) {
            return false;
        }

        $this->settings = $data;
        return $this->saveSettings();
    }

    /**
     * Reset settings
     *
     * @param string|null $layoutId Specific layout or null for all
     * @return bool
     */
    public function reset(?string $layoutId = null): bool
    {
        if ($layoutId === null) {
            $this->settings = [];
        } else {
            unset($this->settings[$layoutId]);
        }

        return $this->saveSettings();
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

