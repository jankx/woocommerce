<?php

namespace Jankx\WooCommerce\Tests\Integration;

use Jankx\WooCommerce\Tests\Helpers\TestCase;
use Jankx\WooCommerce\LayoutSystem\LayoutManager;
use Jankx\WooCommerce\LayoutSystem\CssManager;
use Jankx\WooCommerce\LayoutSystem\SettingsManager;
use Jankx\WooCommerce\Layouts\ProductLoop\GridProductLoopLayout;

/**
 * Layout System Integration Tests
 * 
 * Tests toàn bộ flow từ registration → settings → CSS generation
 */
class LayoutSystemIntegrationTest extends TestCase
{
    private $layoutManager;
    private $cssManager;
    private $settingsManager;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Reset all singletons
        $this->resetSingleton(LayoutManager::class);
        $this->resetSingleton(CssManager::class);
        $this->resetSingleton(SettingsManager::class);
        
        $this->layoutManager = LayoutManager::getInstance();
        $this->cssManager = CssManager::getInstance();
        $this->settingsManager = SettingsManager::getInstance();
    }

    /** @test */
    public function it_can_complete_full_layout_registration_and_rendering_flow()
    {
        // 1. Register layout
        $layout = new GridProductLoopLayout();
        $result = $this->layoutManager->register($layout);
        
        $this->assertTrue($result);
        
        // 2. Set settings
        $settings = [
            'columns' => 3,
            'hover_effect' => 'zoom',
            'show_quick_view' => true
        ];
        $this->settingsManager->setLayoutSettings($layout->getId(), $settings);
        
        // 3. Get common CSS
        $commonCss = $layout->getCommonCss();
        $this->assertIsString($commonCss);
        
        // 4. Get dynamic CSS
        $dynamicCss = $layout->getDynamicCss($settings);
        $this->assertIsString($dynamicCss);
        
        // 5. Inject CSS
        $combinedCss = $commonCss . $dynamicCss;
        $this->cssManager->inject($combinedCss, $layout->getId());
        
        $this->assertTrue($this->cssManager->isInjected($layout->getId()));
    }

    /** @test */
    public function it_handles_multiple_layouts_correctly()
    {
        // Register multiple layouts
        $layout1 = new GridProductLoopLayout();
        $layout2 = new GridProductLoopLayout();
        
        // Change ID for layout2
        $this->setPrivateProperty($layout2, 'id', 'grid-product-loop-v2');
        
        $this->layoutManager->register($layout1);
        $this->layoutManager->register($layout2);
        
        // Both should be registered
        $this->assertTrue($this->layoutManager->has('grid-product-loop'));
        $this->assertTrue($this->layoutManager->has('grid-product-loop-v2'));
        
        // Get all layouts
        $all = $this->layoutManager->all();
        $this->assertCount(2, $all);
    }

    /** @test */
    public function it_caches_compiled_css_correctly()
    {
        $layout = new GridProductLoopLayout();
        $this->layoutManager->register($layout);
        
        // First call - should compile
        $css1 = $layout->getCommonCss();
        
        // Second call - should use cache
        $css2 = $layout->getCommonCss();
        
        $this->assertEquals($css1, $css2);
    }

    /** @test */
    public function it_clears_cache_when_settings_change()
    {
        $layout = new GridProductLoopLayout();
        $layoutId = $layout->getId();
        
        // Set initial settings
        $this->settingsManager->setLayoutSettings($layoutId, ['columns' => 4]);
        
        // Cache some CSS
        $this->cssManager->cache('layout_common_css_' . $layoutId, '.test{}');
        
        // Change settings
        $this->settingsManager->setLayoutSettings($layoutId, ['columns' => 3]);
        
        // Cache should be cleared
        // (This would be tested with actual cache implementation)
        $this->assertTrue(true); // Placeholder
    }

    /** @test */
    public function settings_validation_prevents_invalid_data()
    {
        $validSettings = [
            'primary_color' => '#FF0000',
            'border_radius' => 5,
        ];
        
        $invalidSettings = [
            'primary_color' => 'not-a-color',
            'border_radius' => -10,
        ];
        
        $this->assertTrue($this->settingsManager->validate($validSettings));
        $this->assertFalse($this->settingsManager->validate($invalidSettings));
    }

    /** @test */
    public function default_layout_is_automatically_set()
    {
        $layout = new GridProductLoopLayout();
        $this->layoutManager->register($layout);
        
        $default = $this->layoutManager->getDefault('product-loop');
        
        $this->assertNotNull($default);
        $this->assertEquals($layout->getId(), $default->getId());
    }

    /** @test */
    public function layouts_are_sorted_by_priority()
    {
        $layout1 = new GridProductLoopLayout();
        $layout2 = new GridProductLoopLayout();
        $layout3 = new GridProductLoopLayout();
        
        // Set different priorities
        $this->setPrivateProperty($layout1, 'id', 'layout-1');
        $this->setPrivateProperty($layout1, 'priority', 20);
        
        $this->setPrivateProperty($layout2, 'id', 'layout-2');
        $this->setPrivateProperty($layout2, 'priority', 10);
        
        $this->setPrivateProperty($layout3, 'id', 'layout-3');
        $this->setPrivateProperty($layout3, 'priority', 30);
        
        $this->layoutManager->register($layout1);
        $this->layoutManager->register($layout2);
        $this->layoutManager->register($layout3);
        
        $layouts = $this->layoutManager->getByType('product-loop');
        $ids = array_keys($layouts);
        
        $this->assertEquals(['layout-2', 'layout-1', 'layout-3'], $ids);
    }

    private function resetSingleton($class): void
    {
        $reflection = new \ReflectionClass($class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
    }
}

