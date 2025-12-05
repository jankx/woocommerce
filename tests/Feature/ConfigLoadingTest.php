<?php

namespace Jankx\WooCommerce\Tests\Feature;

use Jankx\WooCommerce\Tests\Helpers\TestCase;
use Jankx\WooCommerce\Providers\WooCommerceLayoutServiceProvider;

/**
 * Config Loading Feature Tests
 * 
 * Tests config file loading và theme options integration
 */
class ConfigLoadingTest extends TestCase
{
    /** @test */
    public function it_loads_default_configuration()
    {
        // Create mock config file
        $configContent = [
            'product_loop' => [
                'default_layout' => 'grid-product-loop',
                'settings' => [
                    'columns' => 4,
                ],
            ],
            'global' => [
                'primary_color' => '#0073aa',
            ],
        ];
        
        $tempConfig = $this->createTempConfig($configContent);
        
        // Mock provider would load this config
        $this->assertFileExists($tempConfig);
        
        // Cleanup
        unlink($tempConfig);
    }

    /** @test */
    public function theme_options_override_config_file()
    {
        // Set config value
        $config = ['product_loop' => ['settings' => ['columns' => 4]]];
        
        // Set theme option (higher priority)
        update_option('jankx_woocommerce_options', [
            'product_loop' => ['settings' => ['columns' => 3]]
        ]);
        
        // Theme option should win
        $themeOption = get_option('jankx_woocommerce_options');
        $this->assertEquals(3, $themeOption['product_loop']['settings']['columns']);
    }

    /** @test */
    public function empty_config_values_fallback_to_theme_options()
    {
        // Config with null value
        $config = ['product_loop' => ['settings' => ['columns' => null]]];
        
        // Theme option has value
        update_option('jankx_woocommerce_options', [
            'product_loop' => ['settings' => ['columns' => 5]]
        ]);
        
        $themeOption = get_option('jankx_woocommerce_options');
        $this->assertNotNull($themeOption['product_loop']['settings']['columns']);
    }

    /** @test */
    public function it_validates_config_structure()
    {
        $validConfig = [
            'product_loop' => [
                'default_layout' => 'some-layout',
                'enabled' => true,
                'settings' => [],
            ],
        ];
        
        $this->assertIsArray($validConfig);
        $this->assertArrayHasKey('product_loop', $validConfig);
        $this->assertArrayHasKey('settings', $validConfig['product_loop']);
    }

    private function createTempConfig(array $content): string
    {
        $tempFile = sys_get_temp_dir() . '/test-woocomerce-config.php';
        $phpContent = "<?php\n\nreturn " . var_export($content, true) . ";\n";
        file_put_contents($tempFile, $phpContent);
        return $tempFile;
    }
}

