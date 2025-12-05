<?php

namespace Jankx\WooCommerce\Tests\Unit;

use Jankx\WooCommerce\Tests\Helpers\TestCase;
use Jankx\WooCommerce\LayoutSystem\SettingsManager;
use Jankx\WooCommerce\Contracts\SettingsInterface;

/**
 * SettingsManager Unit Tests
 */
class SettingsManagerTest extends TestCase
{
    private $settingsManager;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Reset singleton
        $reflection = new \ReflectionClass(SettingsManager::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
        
        $this->settingsManager = SettingsManager::getInstance();
    }

    /** @test */
    public function it_implements_settings_interface()
    {
        $this->assertInstanceOf(SettingsInterface::class, $this->settingsManager);
    }

    /** @test */
    public function it_is_singleton()
    {
        $instance1 = SettingsManager::getInstance();
        $instance2 = SettingsManager::getInstance();
        
        $this->assertSame($instance1, $instance2);
    }

    /** @test */
    public function it_can_set_and_get_settings()
    {
        $this->settingsManager->set('test_key', 'test_value');
        $value = $this->settingsManager->get('test_key');
        
        $this->assertEquals('test_value', $value);
    }

    /** @test */
    public function it_returns_default_for_non_existent_key()
    {
        $value = $this->settingsManager->get('non_existent', 'default_value');
        
        $this->assertEquals('default_value', $value);
    }

    /** @test */
    public function it_supports_dot_notation()
    {
        $this->settingsManager->set('layout.color', 'red');
        $value = $this->settingsManager->get('layout.color');
        
        $this->assertEquals('red', $value);
    }

    /** @test */
    public function it_can_get_layout_settings()
    {
        $settings = [
            'columns' => 4,
            'spacing' => 20,
            'primary_color' => '#0073aa',
        ];
        
        $result = $this->settingsManager->setLayoutSettings('test-layout', $settings);
        $this->assertTrue($result, 'setLayoutSettings should return true');
        
        // Re-initialize để load từ storage (simulate persistence)
        $this->resetSettingsManager();
        
        $retrieved = $this->settingsManager->getLayoutSettings('test-layout');
        
        $this->assertEquals($settings, $retrieved);
    }

    /** @test */
    public function it_returns_all_settings()
    {
        $this->settingsManager->set('key1', 'value1');
        $this->settingsManager->set('key2', 'value2');
        
        $all = $this->settingsManager->all();
        
        $this->assertIsArray($all);
        $this->assertArrayHasKey('key1', $all);
        $this->assertArrayHasKey('key2', $all);
    }

    /** @test */
    public function it_validates_color_settings()
    {
        $validSettings = ['primary_color' => '#FF0000'];
        $invalidSettings = ['primary_color' => 'not-a-color'];
        
        $this->assertTrue($this->settingsManager->validate($validSettings));
        $this->assertFalse($this->settingsManager->validate($invalidSettings));
    }

    /** @test */
    public function it_validates_numeric_settings()
    {
        $validSettings = ['padding' => 20];
        $invalidSettings = ['padding' => -5];
        
        $this->assertTrue($this->settingsManager->validate($validSettings));
        $this->assertFalse($this->settingsManager->validate($invalidSettings));
    }

    /** @test */
    public function it_can_export_settings()
    {
        $this->settingsManager->set('key1', 'value1');
        $exported = $this->settingsManager->export();
        
        $this->assertJson($exported);
        $decoded = json_decode($exported, true);
        $this->assertArrayHasKey('key1', $decoded);
    }

    /** @test */
    public function it_can_import_settings()
    {
        $json = json_encode(['imported_key' => 'imported_value']);
        $result = $this->settingsManager->import($json);
        
        $this->assertTrue($result);
        $this->assertEquals('imported_value', $this->settingsManager->get('imported_key'));
    }

    /** @test */
    public function it_can_reset_all_settings()
    {
        $this->settingsManager->set('key1', 'value1');
        $this->settingsManager->reset();
        
        $all = $this->settingsManager->all();
        $this->assertEmpty($all);
    }

    /** @test */
    public function it_can_reset_specific_layout_settings()
    {
        $this->settingsManager->setLayoutSettings('layout-1', ['primary_color' => '#FF0000']);
        $this->settingsManager->setLayoutSettings('layout-2', ['secondary_color' => '#00FF00']);
        
        $this->settingsManager->reset('layout-1');
        
        // Re-initialize để load từ storage
        $this->resetSettingsManager();
        
        $this->assertEmpty($this->settingsManager->getLayoutSettings('layout-1'));
        $this->assertNotEmpty($this->settingsManager->getLayoutSettings('layout-2'));
    }

    /**
     * Reset settings manager singleton và reload từ storage
     */
    private function resetSettingsManager(): void
    {
        $reflection = new \ReflectionClass(SettingsManager::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
        
        $this->settingsManager = SettingsManager::getInstance();
    }
}

