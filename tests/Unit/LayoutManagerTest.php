<?php

namespace Jankx\WooCommerce\Tests\Unit;

use Jankx\WooCommerce\Tests\Helpers\TestCase;
use Jankx\WooCommerce\LayoutSystem\LayoutManager;
use Jankx\WooCommerce\Contracts\LayoutInterface;
use Jankx\WooCommerce\Contracts\LayoutManagerInterface;

/**
 * LayoutManager Unit Tests
 */
class LayoutManagerTest extends TestCase
{
    private $layoutManager;
    private $mockLayout;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Reset singleton for testing
        $reflection = new \ReflectionClass(LayoutManager::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
        
        $this->layoutManager = LayoutManager::getInstance();
        $this->mockLayout = $this->createMockLayout('test-layout', 'Test Layout', 'test-type');
    }

    /** @test */
    public function it_implements_layout_manager_interface()
    {
        $this->assertInstanceOf(LayoutManagerInterface::class, $this->layoutManager);
    }

    /** @test */
    public function it_is_singleton()
    {
        $instance1 = LayoutManager::getInstance();
        $instance2 = LayoutManager::getInstance();
        
        $this->assertSame($instance1, $instance2);
    }

    /** @test */
    public function it_can_register_a_layout()
    {
        $result = $this->layoutManager->register($this->mockLayout);
        
        $this->assertTrue($result);
        $this->assertTrue($this->layoutManager->has('test-layout'));
    }

    /** @test */
    public function it_can_get_registered_layout()
    {
        $this->layoutManager->register($this->mockLayout);
        $layout = $this->layoutManager->get('test-layout');
        
        $this->assertInstanceOf(LayoutInterface::class, $layout);
        $this->assertEquals('test-layout', $layout->getId());
    }

    /** @test */
    public function it_returns_null_for_non_existent_layout()
    {
        $layout = $this->layoutManager->get('non-existent');
        
        $this->assertNull($layout);
    }

    /** @test */
    public function it_can_unregister_a_layout()
    {
        $this->layoutManager->register($this->mockLayout);
        $result = $this->layoutManager->unregister('test-layout');
        
        $this->assertTrue($result);
        $this->assertFalse($this->layoutManager->has('test-layout'));
    }

    /** @test */
    public function it_can_get_layouts_by_type()
    {
        $layout1 = $this->createMockLayout('layout-1', 'Layout 1', 'type-a');
        $layout2 = $this->createMockLayout('layout-2', 'Layout 2', 'type-a');
        $layout3 = $this->createMockLayout('layout-3', 'Layout 3', 'type-b');
        
        $this->layoutManager->register($layout1);
        $this->layoutManager->register($layout2);
        $this->layoutManager->register($layout3);
        
        $typeALayouts = $this->layoutManager->getByType('type-a');
        
        $this->assertCount(2, $typeALayouts);
        $this->assertArrayHasKey('layout-1', $typeALayouts);
        $this->assertArrayHasKey('layout-2', $typeALayouts);
    }

    /** @test */
    public function it_sorts_layouts_by_priority()
    {
        $layout1 = $this->createMockLayout('layout-1', 'Layout 1', 'test-type', 20);
        $layout2 = $this->createMockLayout('layout-2', 'Layout 2', 'test-type', 10);
        $layout3 = $this->createMockLayout('layout-3', 'Layout 3', 'test-type', 30);
        
        $this->layoutManager->register($layout1);
        $this->layoutManager->register($layout2);
        $this->layoutManager->register($layout3);
        
        $layouts = $this->layoutManager->getByType('test-type');
        $ids = array_keys($layouts);
        
        $this->assertEquals(['layout-2', 'layout-1', 'layout-3'], $ids);
    }

    /** @test */
    public function it_sets_first_layout_as_default()
    {
        $this->layoutManager->register($this->mockLayout);
        $default = $this->layoutManager->getDefault('test-type');
        
        $this->assertNotNull($default);
        $this->assertEquals('test-layout', $default->getId());
    }

    /** @test */
    public function it_can_set_custom_default_layout()
    {
        $layout1 = $this->createMockLayout('layout-1', 'Layout 1', 'test-type');
        $layout2 = $this->createMockLayout('layout-2', 'Layout 2', 'test-type');
        
        $this->layoutManager->register($layout1);
        $this->layoutManager->register($layout2);
        
        $result = $this->layoutManager->setDefault('test-type', 'layout-2');
        $default = $this->layoutManager->getDefault('test-type');
        
        $this->assertTrue($result);
        $this->assertEquals('layout-2', $default->getId());
    }

    /** @test */
    public function it_returns_all_registered_layouts()
    {
        $layout1 = $this->createMockLayout('layout-1', 'Layout 1', 'type-a');
        $layout2 = $this->createMockLayout('layout-2', 'Layout 2', 'type-b');
        
        $this->layoutManager->register($layout1);
        $this->layoutManager->register($layout2);
        
        $all = $this->layoutManager->all();
        
        $this->assertCount(2, $all);
    }

    /** @test */
    public function it_counts_layouts_correctly()
    {
        $layout1 = $this->createMockLayout('layout-1', 'Layout 1', 'type-a');
        $layout2 = $this->createMockLayout('layout-2', 'Layout 2', 'type-a');
        $layout3 = $this->createMockLayout('layout-3', 'Layout 3', 'type-b');
        
        $this->layoutManager->register($layout1);
        $this->layoutManager->register($layout2);
        $this->layoutManager->register($layout3);
        
        $this->assertEquals(3, $this->layoutManager->count());
        $this->assertEquals(2, $this->layoutManager->count('type-a'));
        $this->assertEquals(1, $this->layoutManager->count('type-b'));
    }

    /** @test */
    public function it_prevents_duplicate_registration_by_default()
    {
        $this->layoutManager->register($this->mockLayout);
        $result = $this->layoutManager->register($this->mockLayout);
        
        $this->assertFalse($result);
    }

    /**
     * Create a mock layout for testing
     */
    private function createMockLayout(string $id, string $name, string $type, int $priority = 10): LayoutInterface
    {
        $layout = $this->createMock(LayoutInterface::class);
        
        $layout->method('getId')->willReturn($id);
        $layout->method('getName')->willReturn($name);
        $layout->method('getType')->willReturn($type);
        $layout->method('getPriority')->willReturn($priority);
        $layout->method('getScssPath')->willReturn('');
        $layout->method('getCommonCss')->willReturn('');
        $layout->method('getDynamicCss')->willReturn('');
        $layout->method('render')->willReturn('<div>Mock Layout</div>');
        
        return $layout;
    }
}

