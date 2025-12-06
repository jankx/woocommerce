<?php

namespace Jankx\WooCommerce\Tests\Integration;

use Jankx\WooCommerce\Tests\Helpers\TestCase;
use Jankx\WooCommerce\Hooks\CategoryBlockFilterHook;
use Jankx\WooCommerce\LayoutSystem\LayoutManager;
use Jankx\WooCommerce\Layouts\CategoryBlock\ExpandCollapseCategoryLayout;

/**
 * Category Block Filter Integration Tests
 * 
 * Verify WooCommerce block HTML được transform thành accordion
 */
class CategoryBlockFilterTest extends TestCase
{
    private $filterHook;
    private $layoutManager;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Reset singletons
        $this->resetSingleton(LayoutManager::class);
        
        $this->layoutManager = LayoutManager::getInstance();
        
        // Register expand/collapse layout
        $layout = new ExpandCollapseCategoryLayout();
        $this->layoutManager->register($layout);
        
        // Create filter hook instance
        $this->filterHook = new CategoryBlockFilterHook();
    }

    /** @test */
    public function it_detects_woocommerce_product_categories_block()
    {
        $block = [
            'blockName' => 'woocommerce/product-categories',
            'attrs' => []
        ];
        
        $content = '<ul class="wp-block-categories-list"><li class="cat-item cat-item-1"><a href="/cat/">Test</a></li></ul>';
        
        // Enable accordion
        add_filter('jankx_woocommerce_category_block_use_accordion', '__return_true');
        
        // Transform should process this block
        $result = $this->callPrivateMethod($this->filterHook, 'transformBlockOutput', [$content, $block]);
        
        // Result should be different from input (transformed)
        // Should contain accordion structure
        $this->assertStringContainsString('category-accordion-item', $result);
    }

    /** @test */
    public function it_ignores_non_woocommerce_blocks()
    {
        $block = [
            'blockName' => 'core/paragraph',
            'attrs' => []
        ];
        
        $content = '<p>Test paragraph</p>';
        
        $result = $this->callPrivateMethod($this->filterHook, 'transformBlockOutput', [$content, $block]);
        
        // Should return unchanged
        $this->assertEquals($content, $result);
    }

    /** @test */
    public function it_parses_nested_category_html_correctly()
    {
        $html = '
            <ul class="wp-block-categories-list">
                <li class="cat-item cat-item-1">
                    <a href="/cat1/">Category 1</a>
                    <ul class="children">
                        <li class="cat-item cat-item-2">
                            <a href="/cat2/">Category 2</a>
                        </li>
                    </ul>
                </li>
            </ul>
        ';
        
        $categories = $this->callPrivateMethod($this->filterHook, 'parseHtmlToCategories', [$html]);
        
        // Assert parsed correctly
        $this->assertIsArray($categories);
        $this->assertNotEmpty($categories);
        
        // Assert has parent
        $this->assertEquals(1, $categories[0]['id']);
        $this->assertEquals('Category 1', $categories[0]['name']);
        
        // Assert has children
        $this->assertArrayHasKey('children', $categories[0]);
        $this->assertNotEmpty($categories[0]['children']);
        $this->assertEquals(2, $categories[0]['children'][0]['id']);
    }

    /** @test */
    public function it_detects_current_category_from_classes()
    {
        $html = '
            <ul class="wp-block-categories-list">
                <li class="cat-item cat-item-1 current-cat">
                    <a href="/cat1/" aria-current="page">Current Category</a>
                </li>
            </ul>
        ';
        
        $categories = $this->callPrivateMethod($this->filterHook, 'parseHtmlToCategories', [$html]);
        
        $this->assertTrue($categories[0]['is_current']);
    }

    /** @test */
    public function it_detects_current_ancestor_from_classes()
    {
        $html = '
            <ul class="wp-block-categories-list">
                <li class="cat-item cat-item-1 current-cat-ancestor">
                    <a href="/cat1/">Ancestor Category</a>
                </li>
            </ul>
        ';
        
        $categories = $this->callPrivateMethod($this->filterHook, 'parseHtmlToCategories', [$html]);
        
        $this->assertTrue($categories[0]['is_current_ancestor']);
    }

    /** @test */
    public function transformed_output_contains_accordion_structure()
    {
        // Enable accordion
        add_filter('jankx_woocommerce_category_block_use_accordion', '__return_true');
        
        $block = [
            'blockName' => 'woocommerce/product-categories',
            'attrs' => []
        ];
        
        $html = '<ul class="wp-block-categories-list"><li class="cat-item cat-item-1"><a href="/cat1/">Category 1</a></li></ul>';
        
        $result = $this->callPrivateMethod($this->filterHook, 'transformBlockOutput', [$html, $block]);
        
        // Assert transformed to accordion structure
        $this->assertStringContainsString('jankx-categories-expand-collapse', $result);
        $this->assertStringContainsString('category-accordion-item', $result);
        $this->assertStringContainsString('category-header', $result);
    }

    /** @test */
    public function accordion_not_applied_when_disabled()
    {
        // Disable accordion
        add_filter('jankx_woocommerce_category_block_use_accordion', '__return_false');
        
        $block = [
            'blockName' => 'woocommerce/product-categories',
            'attrs' => []
        ];
        
        $content = '<ul><li>Test</li></ul>';
        
        $result = $this->callPrivateMethod($this->filterHook, 'transformBlockOutput', [$content, $block]);
        
        // Should return original
        $this->assertEquals($content, $result);
    }

    /** @test */
    public function deep_nested_categories_parsed_correctly()
    {
        $html = '
            <ul>
                <li class="cat-item cat-item-1">
                    <a href="/l1/">Level 1</a>
                    <ul class="children">
                        <li class="cat-item cat-item-2">
                            <a href="/l2/">Level 2</a>
                            <ul class="children">
                                <li class="cat-item cat-item-3">
                                    <a href="/l3/">Level 3</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
            </ul>
        ';
        
        $categories = $this->callPrivateMethod($this->filterHook, 'parseHtmlToCategories', [$html]);
        
        // Level 1
        $this->assertEquals(1, $categories[0]['id']);
        
        // Level 2
        $this->assertArrayHasKey('children', $categories[0]);
        $this->assertEquals(2, $categories[0]['children'][0]['id']);
        
        // Level 3
        $this->assertArrayHasKey('children', $categories[0]['children'][0]);
        $this->assertEquals(3, $categories[0]['children'][0]['children'][0]['id']);
    }

    private function resetSingleton($class): void
    {
        $reflection = new \ReflectionClass($class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
    }
}

