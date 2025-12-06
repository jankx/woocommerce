<?php

namespace Jankx\WooCommerce\Tests\Integration;

use Jankx\WooCommerce\Tests\Helpers\TestCase;
use Jankx\WooCommerce\LayoutSystem\CssManager;
use Jankx\WooCommerce\Layouts\ProductLoop\GridProductLoopLayout;

/**
 * Inline CSS Injection Integration Tests
 * 
 * Verify CSS được inject inline vào HTML, KHÔNG external files
 */
class InlineCssInjectionTest extends TestCase
{
    private $cssManager;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Reset singleton
        $this->resetSingleton(CssManager::class);
        $this->cssManager = CssManager::getInstance();
    }

    /** @test */
    public function css_is_injected_inline_not_external()
    {
        // Inject some CSS
        $css = '.test-class { color: red; }';
        $this->cssManager->inject($css, 'test-layout');
        
        // Capture wp_head output
        ob_start();
        $this->cssManager->outputInjectedCss();
        $output = ob_get_clean();
        
        // Assert có <style> tag
        $this->assertStringContainsString('<style', $output);
        $this->assertStringContainsString('type="text/css"', $output);
        $this->assertStringContainsString('id="jankx-woo-inline-css"', $output);
        
        // Assert CSS content trong <style> tag
        $this->assertStringContainsString('.test-class', $output);
        
        // Assert KHÔNG có <link> tag
        $this->assertStringNotContainsString('<link', $output);
        $this->assertStringNotContainsString('rel="stylesheet"', $output);
    }

    /** @test */
    public function multiple_layouts_css_combined_in_single_style_tag()
    {
        // Inject CSS từ multiple layouts
        $this->cssManager->inject('.layout1 { color: red; }', 'layout-1');
        $this->cssManager->inject('.layout2 { color: blue; }', 'layout-2');
        $this->cssManager->inject('.layout3 { color: green; }', 'layout-3');
        
        // Capture output
        ob_start();
        $this->cssManager->outputInjectedCss();
        $output = ob_get_clean();
        
        // Assert chỉ có 1 <style> tag
        $this->assertEquals(1, substr_count($output, '<style'));
        
        // Assert tất cả CSS đều có trong đó
        $this->assertStringContainsString('.layout1', $output);
        $this->assertStringContainsString('.layout2', $output);
        $this->assertStringContainsString('.layout3', $output);
    }

    /** @test */
    public function css_is_minified_in_production()
    {
        // Set devMode = false (production)
        $this->setPrivateProperty($this->cssManager, 'devMode', false);
        
        $css = "
            .test-class {
                color: red;
                font-size: 16px;
            }
            
            /* This is a comment */
            
            .another-class {
                display: block;
            }
        ";
        
        $this->cssManager->inject($css, 'test-layout');
        
        ob_start();
        $this->cssManager->outputInjectedCss();
        $output = ob_get_clean();
        
        // Extract CSS từ <style> tag
        preg_match('/<style[^>]*>(.*?)<\/style>/s', $output, $matches);
        $cssContent = $matches[1] ?? '';
        
        // Assert CSS is minified (no comments in CSS content)
        $this->assertStringNotContainsString('/*', $cssContent);
        
        // Assert CSS is compacted (minimal whitespace)
        $cssContent = trim($cssContent);
        $this->assertStringNotContainsString('  ', $cssContent); // No double spaces
    }

    /** @test */
    public function duplicate_css_not_injected_twice()
    {
        $css = '.test { color: red; }';
        
        // Inject same layout twice
        $this->cssManager->inject($css, 'test-layout');
        $this->cssManager->inject($css, 'test-layout'); // Should be skipped
        
        ob_start();
        $this->cssManager->outputInjectedCss();
        $output = ob_get_clean();
        
        // Should only appear once
        $count = substr_count($output, '.test');
        $this->assertEquals(1, $count);
    }

    /** @test */
    public function layout_css_injection_includes_common_and_dynamic_css()
    {
        $layout = new GridProductLoopLayout();
        
        $settings = [
            'columns' => 3,
            'item_spacing' => 15,
            'border_radius' => 10,
        ];
        
        // Inject layout CSS
        $this->cssManager->injectLayoutCss($layout, $settings);
        
        ob_start();
        $this->cssManager->outputInjectedCss();
        $output = ob_get_clean();
        
        // Assert có CSS
        $this->assertStringContainsString('<style', $output);
        $this->assertNotEmpty(strip_tags($output));
        
        // Assert layout ID được track
        $this->assertTrue($this->cssManager->isInjected($layout->getId()));
    }

    /** @test */
    public function empty_css_does_not_output_style_tag()
    {
        // Don't inject any CSS
        
        ob_start();
        $this->cssManager->outputInjectedCss();
        $output = ob_get_clean();
        
        // Should output nothing or just comment
        $this->assertStringNotContainsString('<style', $output);
    }

    /** @test */
    public function css_stats_track_injection_correctly()
    {
        $this->cssManager->inject('.test1 { color: red; }', 'layout-1');
        $this->cssManager->inject('.test2 { color: blue; }', 'layout-2');
        
        $stats = $this->cssManager->getStats();
        
        $this->assertEquals(2, $stats['layouts_count']);
        $this->assertGreaterThan(0, $stats['total_size']);
        $this->assertArrayHasKey('layouts', $stats);
        $this->assertContains('layout-1', $stats['layouts']);
        $this->assertContains('layout-2', $stats['layouts']);
    }

    /** @test */
    public function inline_css_has_correct_comment_markers()
    {
        $this->cssManager->inject('.test {}', 'test');
        
        ob_start();
        $this->cssManager->outputInjectedCss();
        $output = ob_get_clean();
        
        // Assert có comment markers
        $this->assertStringContainsString('<!-- Jankx WooCommerce Inline CSS -->', $output);
    }

    private function resetSingleton($class): void
    {
        $reflection = new \ReflectionClass($class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
    }
}

