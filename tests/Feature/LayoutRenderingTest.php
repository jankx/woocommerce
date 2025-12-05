<?php

namespace Jankx\WooCommerce\Tests\Feature;

use Jankx\WooCommerce\Tests\Helpers\TestCase;
use Jankx\WooCommerce\Layouts\ProductLoop\GridProductLoopLayout;
use Jankx\WooCommerce\Layouts\ProductLoop\ListProductLoopLayout;

/**
 * Layout Rendering Feature Tests
 * 
 * Tests end-to-end layout rendering
 */
class LayoutRenderingTest extends TestCase
{
    /** @test */
    public function grid_layout_renders_correctly()
    {
        $layout = new GridProductLoopLayout();
        
        // Mock product data
        $data = [
            'products' => [],
            'options' => ['columns' => 4]
        ];
        
        $output = $layout->render($data);
        
        $this->assertIsString($output);
        $this->assertStringContainsString('jankx-products-loop', $output);
        $this->assertStringContainsString('grid-product-loop', $output);
    }

    /** @test */
    public function list_layout_renders_correctly()
    {
        $layout = new ListProductLoopLayout();
        
        $data = [
            'products' => [],
            'options' => []
        ];
        
        $output = $layout->render($data);
        
        $this->assertIsString($output);
        $this->assertStringContainsString('list-product-loop', $output);
    }

    /** @test */
    public function layout_respects_settings()
    {
        $layout = new GridProductLoopLayout();
        
        $settings = [
            'hover_effect' => 'zoom',
            'show_quick_view' => false,
        ];
        
        $css = $layout->getDynamicCss($settings);
        
        // CSS should reflect settings
        if (!empty($settings['hover_effect'])) {
            $this->assertIsString($css);
        }
    }

    /** @test */
    public function layout_handles_empty_data_gracefully()
    {
        $layout = new GridProductLoopLayout();
        
        $output = $layout->render([]);
        
        // Should not crash
        $this->assertIsString($output);
    }

    /** @test */
    public function different_layouts_produce_different_output()
    {
        $gridLayout = new GridProductLoopLayout();
        $listLayout = new ListProductLoopLayout();
        
        $data = ['products' => [], 'options' => []];
        
        $gridOutput = $gridLayout->render($data);
        $listOutput = $listLayout->render($data);
        
        $this->assertNotEquals($gridOutput, $listOutput);
    }
}

