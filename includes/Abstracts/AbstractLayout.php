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
        $output = ob_get_clean();

        // Add fingerprint for debugging
        return $this->wrapWithFingerprint($output);
    }

    /**
     * Get fingerprint data for debugging
     *
     * @return array
     */
    protected function getFingerprintData(): array
    {
        return [
            'layout_id' => $this->id,
            'layout_type' => $this->type,
            'layout_name' => $this->name,
            'layout_class' => get_class($this),
            'priority' => $this->priority,
            'timestamp' => time(),
        ];
    }

    /**
     * Generate fingerprint comment
     *
     * @return string
     */
    protected function generateFingerprintComment(): string
    {
        $data = $this->getFingerprintData();
        $fingerprint = sprintf(
            'JANKX_WOO_LAYOUT: id=%s | type=%s | name=%s | class=%s | priority=%d | time=%s',
            $data['layout_id'],
            $data['layout_type'],
            $data['layout_name'],
            $data['layout_class'],
            $data['priority'],
            date('Y-m-d H:i:s', $data['timestamp'])
        );

        return sprintf(
            "\n<!-- %s -->\n",
            $fingerprint
        );
    }

    /**
     * Generate fingerprint data attribute
     *
     * @return string
     */
    protected function generateFingerprintAttributes(): string
    {
        $data = $this->getFingerprintData();
        return sprintf(
            ' data-jankx-layout-id="%s" data-jankx-layout-type="%s" data-jankx-layout-name="%s" data-jankx-layout-priority="%d"',
            esc_attr($data['layout_id']),
            esc_attr($data['layout_type']),
            esc_attr($data['layout_name']),
            $data['priority']
        );
    }

    /**
     * Wrap output with fingerprint
     *
     * @param string $output
     * @return string
     */
    protected function wrapWithFingerprint(string $output): string
    {
        $fingerprintComment = $this->generateFingerprintComment();
        
        // Try to inject data attributes into first wrapper element
        $outputWithAttributes = $this->injectFingerprintAttributes($output);
        
        // Generate console.log script
        $consoleLogScript = $this->generateConsoleLogScript();
        
        return $fingerprintComment . $outputWithAttributes . $consoleLogScript . $fingerprintComment;
    }

    /**
     * Generate console.log script for debugging
     *
     * @return string
     */
    protected function generateConsoleLogScript(): string
    {
        $data = $this->getFingerprintData();
        
        // Generate unique ID for this layout instance
        $instanceId = 'jankx-layout-' . $this->id . '-' . uniqid();
        
        // Create formatted console.log with styled output
        $script = sprintf(
            '<script type="text/javascript">(function(){' .
            'if(typeof console!==\'undefined\'&&console.log){' .
            'console.log(\'%%c[Jankx WooCommerce Layout]%%c %s\',\'color:#4CAF50;font-weight:bold;font-size:12px;padding:2px 4px;background:#E8F5E9;border-radius:3px\',\'color:#333;font-size:11px\',{' .
            'id:\'%s\',' .
            'type:\'%s\',' .
            'name:\'%s\',' .
            'class:\'%s\',' .
            'priority:%d,' .
            'timestamp:\'%s\',' .
            'instance:\'%s\'' .
            '});' .
            '}})();</script>',
            esc_js($data['layout_name']),
            esc_js($data['layout_id']),
            esc_js($data['layout_type']),
            esc_js($data['layout_name']),
            esc_js($data['layout_class']),
            $data['priority'],
            esc_js(date('Y-m-d H:i:s', $data['timestamp'])),
            esc_js($instanceId)
        );
        
        return $script;
    }

    /**
     * Inject fingerprint data attributes into first wrapper element
     *
     * @param string $html
     * @return string
     */
    protected function injectFingerprintAttributes(string $html): string
    {
        $attributes = $this->generateFingerprintAttributes();
        
        // Pattern to match first opening tag (div, section, article, etc.)
        $pattern = '/<(\w+)([^>]*?)(\s*class\s*=\s*["\'][^"\']*["\'])?([^>]*?)>/i';
        
        $replacement = function($matches) use ($attributes) {
            $tag = $matches[1];
            $beforeClass = $matches[2];
            $classAttr = $matches[3] ?? '';
            $afterClass = $matches[4] ?? '';
            
            // Check if attributes already exist
            if (strpos($matches[0], 'data-jankx-layout-id') !== false) {
                return $matches[0]; // Already has fingerprint
            }
            
            // Inject after class attribute if exists, otherwise after tag name
            if (!empty($classAttr)) {
                return '<' . $tag . $beforeClass . $classAttr . $attributes . $afterClass . '>';
            } else {
                return '<' . $tag . $beforeClass . $attributes . $afterClass . '>';
            }
        };
        
        // Only replace first occurrence
        $count = 0;
        return preg_replace_callback($pattern, function($matches) use ($attributes, &$count) {
            if ($count++ > 0) {
                return $matches[0]; // Skip after first match
            }
            
            $tag = $matches[1];
            $beforeClass = $matches[2];
            $classAttr = $matches[3] ?? '';
            $afterClass = $matches[4] ?? '';
            
            // Check if attributes already exist
            if (strpos($matches[0], 'data-jankx-layout-id') !== false) {
                return $matches[0];
            }
            
            // Inject after class attribute if exists, otherwise after tag name
            if (!empty($classAttr)) {
                return '<' . $tag . $beforeClass . $classAttr . $attributes . $afterClass . '>';
            } else {
                return '<' . $tag . $beforeClass . $attributes . $afterClass . '>';
            }
        }, $html, 1);
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

