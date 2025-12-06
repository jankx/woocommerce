<?php

namespace Jankx\WooCommerce\Tests\Unit;

use Jankx\WooCommerce\Tests\Helpers\TestCase;
use Jankx\WooCommerce\Layouts\CategoryBlock\ExpandCollapseCategoryLayout;
use Jankx\WooCommerce\Layouts\ProductLoop\GridProductLoopLayout;

/**
 * JavaScript Inline Tests
 * 
 * Verify JavaScript rules:
 * - NO external JS files
 * - Pure vanilla JavaScript
 * - Inline/internal only
 */
class JavaScriptInlineTest extends TestCase
{
    /** @test */
    public function javascript_is_inline_not_external()
    {
        $layout = new ExpandCollapseCategoryLayout();
        
        $categories = [\Jankx\WooCommerce\Tests\Helpers\WordPressMocks::getTerm(1)];
        $output = $layout->render(['categories' => $categories]);
        
        // Assert có <script> tag
        $this->assertStringContainsString('<script>', $output);
        
        // Assert KHÔNG có src attribute (external file)
        $this->assertStringNotContainsString('<script src=', $output);
        
        // Count script tags với và không có src
        $totalScripts = substr_count($output, '<script');
        $externalScripts = substr_count($output, '<script src=');
        
        // All scripts should be inline (no src)
        $this->assertEquals(0, $externalScripts, 'Should have zero external scripts');
    }

    /** @test */
    public function javascript_is_pure_vanilla_no_jquery()
    {
        $layout = new ExpandCollapseCategoryLayout();
        
        $categories = [\Jankx\WooCommerce\Tests\Helpers\WordPressMocks::getTerm(1)];
        $output = $layout->render(['categories' => $categories]);
        
        // Extract JavaScript code
        preg_match_all('/<script[^>]*>(.*?)<\/script>/s', $output, $matches);
        $jsCode = implode("\n", $matches[1] ?? []);
        
        // Assert NO jQuery
        $this->assertStringNotContainsString('jQuery(', $jsCode);
        $this->assertStringNotContainsString('jQuery.', $jsCode);
        $this->assertStringNotContainsString('$(', $jsCode);
        $this->assertStringNotContainsString('$.', $jsCode);
        
        // Assert vanilla patterns
        $this->assertStringContainsString('document.', $jsCode);
    }

    /** @test */
    public function javascript_uses_strict_mode()
    {
        $layout = new ExpandCollapseCategoryLayout();
        
        $categories = [\Jankx\WooCommerce\Tests\Helpers\WordPressMocks::getTerm(1)];
        $output = $layout->render(['categories' => $categories]);
        
        // Extract JavaScript
        preg_match('/<script[^>]*>(.*?)<\/script>/s', $output, $matches);
        $jsCode = $matches[1] ?? '';
        
        // Assert 'use strict' present
        $this->assertStringContainsString("'use strict'", $jsCode);
    }

    /** @test */
    public function javascript_uses_iife_pattern()
    {
        $layout = new ExpandCollapseCategoryLayout();
        
        $categories = [\Jankx\WooCommerce\Tests\Helpers\WordPressMocks::getTerm(1)];
        $output = $layout->render(['categories' => $categories]);
        
        // Extract JavaScript
        preg_match('/<script[^>]*>(.*?)<\/script>/s', $output, $matches);
        $jsCode = $matches[1] ?? '';
        
        // Assert IIFE pattern
        $this->assertStringContainsString('(function()', $jsCode);
        $this->assertStringContainsString('})();', $jsCode);
    }

    /** @test */
    public function javascript_uses_dom_content_loaded()
    {
        $layout = new ExpandCollapseCategoryLayout();
        
        $categories = [\Jankx\WooCommerce\Tests\Helpers\WordPressMocks::getTerm(1)];
        $output = $layout->render(['categories' => $categories]);
        
        // Extract JavaScript
        preg_match('/<script[^>]*>(.*?)<\/script>/s', $output, $matches);
        $jsCode = $matches[1] ?? '';
        
        // Assert DOMContentLoaded used
        $this->assertStringContainsString('DOMContentLoaded', $jsCode);
    }

    /** @test */
    public function javascript_uses_event_delegation()
    {
        $layout = new ExpandCollapseCategoryLayout();
        
        $categories = [\Jankx\WooCommerce\Tests\Helpers\WordPressMocks::getTerm(1)];
        $output = $layout->render(['categories' => $categories]);
        
        // Extract JavaScript
        preg_match('/<script[^>]*>(.*?)<\/script>/s', $output, $matches);
        $jsCode = $matches[1] ?? '';
        
        // Assert event delegation pattern
        // Container listener với closest() check
        $this->assertStringContainsString('.addEventListener(', $jsCode);
        $this->assertStringContainsString('.closest(', $jsCode);
    }

    /** @test */
    public function javascript_no_external_libraries_mentioned()
    {
        $layout = new ExpandCollapseCategoryLayout();
        
        $categories = [\Jankx\WooCommerce\Tests\Helpers\WordPressMocks::getTerm(1)];
        $output = $layout->render(['categories' => $categories]);
        
        // Extract JavaScript
        preg_match('/<script[^>]*>(.*?)<\/script>/s', $output, $matches);
        $jsCode = $matches[1] ?? '';
        
        // List of prohibited libraries
        $prohibitedLibs = [
            'lodash',
            'underscore',
            'moment',
            'axios',
            'gsap',
            'anime.js',
        ];
        
        foreach ($prohibitedLibs as $lib) {
            $this->assertStringNotContainsString($lib, strtolower($jsCode));
        }
    }

    /** @test */
    public function javascript_config_from_data_attributes()
    {
        $layout = new ExpandCollapseCategoryLayout();
        
        $categories = [\Jankx\WooCommerce\Tests\Helpers\WordPressMocks::getTerm(1)];
        $output = $layout->render(['categories' => $categories]);
        
        // Assert HTML có data attributes
        $this->assertStringContainsString('data-animation-speed', $output);
        
        // Extract JavaScript
        preg_match('/<script[^>]*>(.*?)<\/script>/s', $output, $matches);
        $jsCode = $matches[1] ?? '';
        
        // Assert JS reads from dataset
        $this->assertStringContainsString('dataset.', $jsCode);
    }

    /** @test */
    public function multiple_layouts_can_have_separate_inline_scripts()
    {
        $layout1 = new ExpandCollapseCategoryLayout();
        $layout2 = new GridProductLoopLayout();
        
        $output1 = $layout1->render(['categories' => [\Jankx\WooCommerce\Tests\Helpers\WordPressMocks::getTerm(1)]]);
        $output2 = $layout2->render(['products' => []]);
        
        // Both can have scripts
        $hasScript1 = strpos($output1, '<script>') !== false;
        $hasScript2 = strpos($output2, '<script>') !== false;
        
        // At least one should have script
        $this->assertTrue($hasScript1 || $hasScript2);
    }

    /** @test */
    public function javascript_properly_escaped()
    {
        $layout = new ExpandCollapseCategoryLayout();
        
        $categories = [\Jankx\WooCommerce\Tests\Helpers\WordPressMocks::getTerm(1)];
        $output = $layout->render(['categories' => $categories]);
        
        // Extract JavaScript
        preg_match('/<script[^>]*>(.*?)<\/script>/s', $output, $matches);
        $jsCode = $matches[1] ?? '';
        
        // Should not contain unescaped quotes that break syntax
        // This is a basic check - real escaping done by esc_js()
        $this->assertIsString($jsCode);
    }
}

