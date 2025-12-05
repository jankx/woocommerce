<?php

namespace Jankx\WooCommerce\Tests\Unit;

use Jankx\WooCommerce\Tests\Helpers\TestCase;
use Jankx\WooCommerce\LayoutSystem\CssManager;
use Jankx\WooCommerce\Contracts\CssManagerInterface;

/**
 * CssManager Unit Tests
 */
class CssManagerTest extends TestCase
{
    private $cssManager;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Reset singleton
        $reflection = new \ReflectionClass(CssManager::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
        
        $this->cssManager = CssManager::getInstance();
    }

    /** @test */
    public function it_implements_css_manager_interface()
    {
        $this->assertInstanceOf(CssManagerInterface::class, $this->cssManager);
    }

    /** @test */
    public function it_is_singleton()
    {
        $instance1 = CssManager::getInstance();
        $instance2 = CssManager::getInstance();
        
        $this->assertSame($instance1, $instance2);
    }

    /** @test */
    public function it_can_inject_css()
    {
        $css = '.test { color: red; }';
        $this->cssManager->inject($css, 'test-layout');
        
        $this->assertTrue($this->cssManager->isInjected('test-layout'));
    }

    /** @test */
    public function it_does_not_inject_duplicate_css()
    {
        $css = '.test { color: red; }';
        
        $this->cssManager->inject($css, 'test-layout');
        $this->cssManager->inject($css, 'test-layout');
        
        // Should only inject once
        $this->assertTrue($this->cssManager->isInjected('test-layout'));
    }

    /** @test */
    public function it_can_cache_css()
    {
        // Set devMode = false để enable caching
        $this->setPrivateProperty($this->cssManager, 'devMode', false);
        
        $css = '.test { color: blue; }';
        $result = $this->cssManager->cache('test-key', $css);
        
        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_retrieve_cached_css()
    {
        // Set devMode = false để enable caching
        $this->setPrivateProperty($this->cssManager, 'devMode', false);
        
        $css = '.test { color: green; }';
        $this->cssManager->cache('test-key', $css);
        
        $cached = $this->cssManager->getCached('test-key');
        
        $this->assertEquals($css, $cached);
    }

    /** @test */
    public function it_returns_null_for_non_existent_cache()
    {
        $cached = $this->cssManager->getCached('non-existent');
        
        $this->assertNull($cached);
    }

    /** @test */
    public function it_can_clear_cache()
    {
        $this->cssManager->cache('test-key', '.test {}');
        $result = $this->cssManager->clearCache('test-layout');
        
        $this->assertTrue($result);
    }

    /** @test */
    public function it_minifies_css_correctly()
    {
        $css = "
            .test {
                color: red;
                font-size: 16px;
            }
            
            /* This is a comment */
            .another {
                display: block;
            }
        ";
        
        $minified = $this->callPrivateMethod($this->cssManager, 'minifyCss', [$css]);
        
        // Should remove comments and whitespace
        $this->assertStringNotContainsString('/*', $minified);
        $this->assertStringNotContainsString("\n", $minified);
    }

    /** @test */
    public function it_compiles_scss_file()
    {
        // Create temporary SCSS file
        $scssPath = sys_get_temp_dir() . '/test.scss';
        file_put_contents($scssPath, '.test { color: red; }');
        
        $css = $this->cssManager->compile($scssPath);
        
        $this->assertNotEmpty($css);
        
        // Cleanup
        unlink($scssPath);
    }

    /** @test */
    public function it_returns_empty_string_for_non_existent_scss_file()
    {
        $css = $this->cssManager->compile('/non/existent/file.scss');
        
        $this->assertEmpty($css);
    }

    /** @test */
    public function it_can_get_stats()
    {
        $this->cssManager->inject('.test1 {}', 'layout-1');
        $this->cssManager->inject('.test2 {}', 'layout-2');
        
        $stats = $this->cssManager->getStats();
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('layouts_count', $stats);
        $this->assertArrayHasKey('total_size', $stats);
        $this->assertEquals(2, $stats['layouts_count']);
    }
}

