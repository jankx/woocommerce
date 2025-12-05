<?php

namespace Jankx\WooCommerce\Abstracts;

use Jankx\WooCommerce\Contracts\LayoutInterface;

/**
 * Abstract Class AbstractLayout
 * 
 * Base class cho tất cả layouts, implement các logic chung
 */
abstract class AbstractLayout implements LayoutInterface
{
    /**
     * @var string Layout ID
     */
    protected $id;

    /**
     * @var string Layout name
     */
    protected $name;

    /**
     * @var string Layout type
     */
    protected $type;

    /**
     * @var int Priority
     */
    protected $priority = 10;

    /**
     * @var array Supported settings
     */
    protected $supportedSettings = [];

    /**
     * @var string SCSS file path
     */
    protected $scssPath = '';

    /**
     * @var array Template variables
     */
    protected $templateVars = [];

    /**
     * Constructor
     *
     * @param string $id Layout ID
     * @param string $name Layout name
     * @param string $type Layout type
     */
    public function __construct(string $id, string $name, string $type)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->init();
    }

    /**
     * Initialize layout (hook cho child classes)
     *
     * @return void
     */
    protected function init(): void
    {
        // Override trong child class nếu cần
    }

    /**
     * {@inheritDoc}
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * {@inheritDoc}
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * Set priority
     *
     * @param int $priority
     * @return self
     */
    public function setPriority(int $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getScssPath(): string
    {
        if (empty($this->scssPath)) {
            // Default SCSS path dựa trên type và id
            $this->scssPath = $this->getDefaultScssPath();
        }
        return $this->scssPath;
    }

    /**
     * Get default SCSS path
     *
     * @return string
     */
    protected function getDefaultScssPath(): string
    {
        return sprintf(
            '%s/assets/scss/layouts/%s/%s.scss',
            dirname(dirname(__DIR__)),
            $this->type,
            $this->id
        );
    }

    /**
     * Set SCSS path
     *
     * @param string $path
     * @return self
     */
    public function setScssPath(string $path): self
    {
        $this->scssPath = $path;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsetting(string $settingKey): bool
    {
        return in_array($settingKey, $this->supportedSettings, true);
    }

    /**
     * Add supported setting
     *
     * @param string $settingKey
     * @return self
     */
    public function addSupportedSetting(string $settingKey): self
    {
        if (!in_array($settingKey, $this->supportedSettings, true)) {
            $this->supportedSettings[] = $settingKey;
        }
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCommonCss(): string
    {
        // Sẽ được compile từ SCSS file
        $cssManager = $this->getCssManager();
        $scssPath = $this->getScssPath();

        if (!file_exists($scssPath)) {
            return '';
        }

        // Check cache first
        $cacheKey = 'layout_common_css_' . $this->id;
        $cached = $cssManager->getCached($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        // Compile và cache
        $css = $cssManager->compile($scssPath);
        $cssManager->cache($cacheKey, $css);

        return $css;
    }

    /**
     * {@inheritDoc}
     */
    public function getDynamicCss(array $settings = []): string
    {
        // Override trong child class để generate CSS từ settings
        return $this->generateDynamicCss($settings);
    }

    /**
     * Generate dynamic CSS từ settings
     * 
     * Child classes override method này để tạo CSS động
     *
     * @param array $settings
     * @return string
     */
    protected function generateDynamicCss(array $settings): string
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    abstract public function render(array $data = []): string;

    /**
     * Get template path
     *
     * @return string
     */
    protected function getTemplatePath(): string
    {
        return sprintf(
            '%s/templates/layouts/%s/%s.php',
            dirname(dirname(__DIR__)),
            $this->type,
            $this->id
        );
    }

    /**
     * Load template with data
     *
     * @param string $templatePath
     * @param array $data
     * @return string
     */
    protected function loadTemplate(string $templatePath, array $data = []): string
    {
        if (!file_exists($templatePath)) {
            return sprintf('<!-- Template not found: %s -->', $templatePath);
        }

        // Extract data to variables
        extract(array_merge($this->templateVars, $data), EXTR_SKIP);

        // Start output buffering
        ob_start();
        include $templatePath;
        return ob_get_clean();
    }

    /**
     * Get CSS Manager instance
     *
     * @return \Jankx\WooCommerce\Contracts\CssManagerInterface
     */
    protected function getCssManager()
    {
        return \Jankx\WooCommerce\LayoutSystem\CssManager::getInstance();
    }

    /**
     * Set template variable
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function setTemplateVar(string $key, $value): self
    {
        $this->templateVars[$key] = $value;
        return $this;
    }
}

