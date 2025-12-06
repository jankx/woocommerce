<?php

namespace Jankx\WooCommerce\Tests\Unit;

use Jankx\WooCommerce\Tests\Helpers\TestCase;
use Jankx\WooCommerce\Layouts\ProductLoop\GridProductLoopLayout;
use Jankx\WooCommerce\Layouts\CategoryBlock\ExpandCollapseCategoryLayout;
use Jankx\WooCommerce\LayoutSystem\SettingsManager;

/**
 * Dynamic CSS Generation Tests
 * 
 * Verify CSS được generate từ PHP settings
 */
class DynamicCssGenerationTest extends TestCase
{
    /** @test */
    public function layout_generates_css_from_settings()
    {
        $layout = new GridProductLoopLayout();
        
        $settings = [
            'item_spacing' => 20,
            'border_radius' => 5,
        ];
        
        $css = $layout->getDynamicCss($settings);
        
        // Assert CSS generated
        $this->assertIsString($css);
        $this->assertNotEmpty($css);
        
        // Assert contains settings values
        $this->assertStringContainsString('20px', $css); // item_spacing
        $this->assertStringContainsString('5px', $css); // border_radius
    }

    /** @test */
    public function dynamic_css_includes_layout_specific_selectors()
    {
        $layout = new GridProductLoopLayout();
        $layoutId = $layout->getId();
        
        $settings = ['item_spacing' => 10];
        $css = $layout->getDynamicCss($settings);
        
        // Assert CSS có layout-specific selector
        $this->assertStringContainsString($layoutId, $css);
        $this->assertStringContainsString('.product-loop-', $css);
    }

    /** @test */
    public function empty_settings_returns_empty_or_safe_css()
    {
        $layout = new GridProductLoopLayout();
        
        $css = $layout->getDynamicCss([]);
        
        // Should not break, return empty or safe CSS
        $this->assertIsString($css);
    }

    /** @test */
    public function settings_manager_generates_css_for_layout()
    {
        // Reset singleton
        $reflection = new \ReflectionClass(SettingsManager::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
        
        $settingsManager = SettingsManager::getInstance();
        $layout = new GridProductLoopLayout();
        
        // Set settings
        $settings = [
            'primary_color' => '#FF0000',
            'padding' => 20,
        ];
        
        $settingsManager->setLayoutSettings($layout->getId(), $settings);
        
        // Generate CSS
        $css = $settingsManager->generateCss($layout->getId());
        
        // Assert CSS generated
        $this->assertIsString($css);
    }

    /** @test */
    public function expand_collapse_layout_generates_accordion_css()
    {
        $layout = new ExpandCollapseCategoryLayout();
        
        $settings = [
            'accordion_style' => 'card',
            'animation_speed' => 250,
        ];
        
        $css = $layout->getDynamicCss($settings);
        
        // Assert có accordion-specific CSS
        $this->assertStringContainsString('250ms', $css); // animation speed
    }

    /** @test */
    public function color_settings_generate_valid_css()
    {
        $layout = new GridProductLoopLayout();
        
        $settings = [
            'primary_color' => '#0073aa',
        ];
        
        $css = $this->callPrivateMethod($layout, 'generateDynamicCss', [$settings]);
        
        // Color should be in CSS
        if (!empty($css)) {
            $this->assertStringContainsString('#0073aa', $css);
        }
        
        // Should be valid CSS (no syntax errors)
        $this->assertDoesNotMatchRegularExpression('/[{}]{2,}/', $css); // No double braces
    }

    /** @test */
    public function numeric_settings_generate_valid_css()
    {
        $layout = new GridProductLoopLayout();
        
        $settings = [
            'item_spacing' => 15,
            'border_radius' => 8,
        ];
        
        $css = $this->callPrivateMethod($layout, 'generateDynamicCss', [$settings]);
        
        // Numbers should be in CSS with units
        $this->assertStringContainsString('15px', $css);
        $this->assertStringContainsString('8px', $css);
    }

    /** @test */
    public function boolean_settings_affect_css_output()
    {
        $layout = new ExpandCollapseCategoryLayout();
        
        // Hide product preview
        $settings = [
            'show_product_preview' => false,
        ];
        
        $css = $this->callPrivateMethod($layout, 'generateDynamicCss', [$settings]);
        
        // Should contain display: none for products-preview
        $this->assertStringContainsString('.products-preview', $css);
        $this->assertStringContainsString('display: none', $css);
    }
}

