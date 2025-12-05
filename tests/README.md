# Jankx WooCommerce Layout System - Test Suite

## Tổng quan

Test suite hoàn chỉnh cho WooCommerce Layout System bao gồm:
- **Unit Tests**: Test các components riêng lẻ
- **Integration Tests**: Test tương tác giữa components
- **Feature Tests**: Test end-to-end workflows

## Cài đặt

### 1. Install PHPUnit

```bash
composer require --dev phpunit/phpunit:^9.5
```

### 2. Install Dependencies (Optional)

```bash
# Nếu muốn dùng WordPress test suite thật
composer require --dev wp-phpunit/wp-phpunit

# Hoặc dùng Brain Monkey để mock WordPress
composer require --dev brain/monkey
```

## Chạy Tests

### Chạy tất cả tests

```bash
cd vendor/jankx/woocommerce
vendor/bin/phpunit
```

### Chạy specific test suite

```bash
# Unit tests only
vendor/bin/phpunit --testsuite "Unit Tests"

# Integration tests only
vendor/bin/phpunit --testsuite "Integration Tests"

# Feature tests only
vendor/bin/phpunit --testsuite "Feature Tests"
```

### Chạy specific test file

```bash
vendor/bin/phpunit tests/Unit/LayoutManagerTest.php
```

### Chạy specific test method

```bash
vendor/bin/phpunit --filter it_can_register_a_layout
```

## Code Coverage

### Generate coverage report

```bash
# HTML report
vendor/bin/phpunit --coverage-html tests/coverage/html

# Text report
vendor/bin/phpunit --coverage-text

# Clover XML (for CI)
vendor/bin/phpunit --coverage-clover tests/coverage/clover.xml
```

### View coverage

```bash
# Open HTML report
open tests/coverage/html/index.html
```

## Test Structure

```
tests/
├── bootstrap.php              # Bootstrap file
├── phpunit.xml               # PHPUnit configuration
├── Helpers/                  # Test helpers
│   ├── TestCase.php         # Base test case
│   └── WordPressMocks.php   # WordPress function mocks
├── Unit/                    # Unit tests
│   ├── LayoutManagerTest.php
│   ├── CssManagerTest.php
│   └── SettingsManagerTest.php
├── Integration/             # Integration tests
│   └── LayoutSystemIntegrationTest.php
└── Feature/                 # Feature tests
    ├── ConfigLoadingTest.php
    └── LayoutRenderingTest.php
```

## Writing Tests

### Unit Test Example

```php
<?php
namespace Jankx\WooCommerce\Tests\Unit;

use Jankx\WooCommerce\Tests\Helpers\TestCase;

class MyComponentTest extends TestCase
{
    /** @test */
    public function it_does_something()
    {
        $component = new MyComponent();
        $result = $component->doSomething();
        
        $this->assertTrue($result);
    }
}
```

### Integration Test Example

```php
<?php
namespace Jankx\WooCommerce\Tests\Integration;

use Jankx\WooCommerce\Tests\Helpers\TestCase;

class MyIntegrationTest extends TestCase
{
    /** @test */
    public function it_integrates_components()
    {
        $manager = LayoutManager::getInstance();
        $layout = new GridProductLoopLayout();
        
        $manager->register($layout);
        
        $this->assertTrue($manager->has($layout->getId()));
    }
}
```

## Test Helpers

### TestCase Base Class

Provides helpful methods:

```php
// Get private property
$value = $this->getPrivateProperty($object, 'propertyName');

// Set private property
$this->setPrivateProperty($object, 'propertyName', $value);

// Call private method
$result = $this->callPrivateMethod($object, 'methodName', [$arg1, $arg2]);

// Assert trait usage
$this->assertClassUsesTrait(TraitName::class, $object);

// Assert interface implementation
$this->assertClassImplementsInterface(InterfaceName::class, $object);
```

### WordPress Mocks

Mock WordPress functions automatically:

```php
// Options
get_option('key', 'default');
update_option('key', 'value');
delete_option('key');

// Transients
get_transient('key');
set_transient('key', 'value', 3600);
delete_transient('key');

// Hooks
add_action('hook', $callback);
do_action('hook', $arg);
add_filter('hook', $callback);
apply_filters('hook', $value);
```

### Reset State Between Tests

```php
use Jankx\WooCommerce\Tests\Helpers\WordPressMocks;

protected function setUp(): void
{
    parent::setUp();
    WordPressMocks::reset(); // Reset all mocks
}
```

## Continuous Integration

### GitHub Actions Example

```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '7.4'
        extensions: mbstring, xml
        coverage: xdebug
    
    - name: Install dependencies
      run: composer install
    
    - name: Run tests
      run: vendor/bin/phpunit --coverage-clover coverage.xml
    
    - name: Upload coverage
      uses: codecov/codecov-action@v2
      with:
        file: ./coverage.xml
```

## Test Coverage Goals

### Current Coverage

- **LayoutManager**: ~90%
- **CssManager**: ~85%
- **SettingsManager**: ~90%
- **Service Providers**: ~75%
- **Layouts**: ~80%

### Target Coverage

- Overall: **85%+**
- Critical paths: **95%+**
- New code: **100%**

## Best Practices

### 1. Test Names

Use descriptive names:
```php
/** @test */
public function it_registers_layout_successfully() // Good

/** @test */
public function test1() // Bad
```

### 2. Arrange-Act-Assert Pattern

```php
/** @test */
public function it_does_something()
{
    // Arrange
    $manager = LayoutManager::getInstance();
    $layout = new GridLayout();
    
    // Act
    $result = $manager->register($layout);
    
    // Assert
    $this->assertTrue($result);
}
```

### 3. One Assertion Per Test

Prefer multiple specific tests over one test with many assertions.

### 4. Test Edge Cases

```php
/** @test */
public function it_handles_empty_data() { }

/** @test */
public function it_handles_null_values() { }

/** @test */
public function it_handles_invalid_input() { }
```

### 5. Mock External Dependencies

```php
$mockLayout = $this->createMock(LayoutInterface::class);
$mockLayout->method('getId')->willReturn('test-id');
```

## Debugging Tests

### Verbose output

```bash
vendor/bin/phpunit --verbose
```

### Stop on failure

```bash
vendor/bin/phpunit --stop-on-failure
```

### Debug specific test

```bash
vendor/bin/phpunit --filter test_name --debug
```

### Print output

```php
/** @test */
public function it_debugs()
{
    var_dump($someVariable);
    fwrite(STDERR, print_r($data, true));
}
```

## Troubleshooting

### Issue: Class not found

```bash
# Regenerate autoloader
composer dump-autoload
```

### Issue: WordPress functions undefined

```bash
# Check WordPressMocks.php is loaded in bootstrap.php
```

### Issue: Singleton conflicts

```bash
# Reset singletons in setUp()
$reflection = new \ReflectionClass(ClassName::class);
$instance = $reflection->getProperty('instance');
$instance->setAccessible(true);
$instance->setValue(null, null);
```

## Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [WordPress Testing](https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/)
- [Test Driven Development](https://martinfowler.com/bliki/TestDrivenDevelopment.html)

---

**Happy Testing! 🧪**

