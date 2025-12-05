# Testing Guide - Jankx WooCommerce Layout System

## 📋 Tổng quan Test Suite

Test suite hoàn chỉnh bao gồm:

### ✅ Unit Tests (7 test cases)
- **LayoutManagerTest** - 13 tests
- **CssManagerTest** - 11 tests  
- **SettingsManagerTest** - 12 tests

### ✅ Integration Tests (2 test cases)
- **LayoutSystemIntegrationTest** - 8 tests
- End-to-end workflows

### ✅ Feature Tests (2 test cases)
- **ConfigLoadingTest** - 4 tests
- **LayoutRenderingTest** - 5 tests

**Total: 53 tests** covering critical functionality

## 🚀 Quick Start

### 1. Install Dependencies

```bash
cd vendor/jankx/woocommerce
composer install --dev
```

### 2. Run Tests

```bash
# All tests
make test

# Or using composer
composer test

# Or using PHPUnit directly
vendor/bin/phpunit
```

## 📊 Test Commands

### Via Make

```bash
make test              # Run all tests
make test-unit         # Unit tests only
make test-integration  # Integration tests only
make test-feature      # Feature tests only
make coverage          # Generate HTML coverage report
make coverage-text     # Show coverage in terminal
make test-filter FILTER=test_name  # Run specific test
```

### Via Composer

```bash
composer test              # All tests
composer test:unit         # Unit tests
composer test:integration  # Integration tests
composer test:feature      # Feature tests
composer test:coverage     # Coverage report
```

### Via PHPUnit

```bash
vendor/bin/phpunit                           # All tests
vendor/bin/phpunit --testsuite "Unit Tests" # Unit only
vendor/bin/phpunit --filter test_name       # Specific test
vendor/bin/phpunit --stop-on-failure        # Stop on first failure
vendor/bin/phpunit --testdox                # Readable output
```

## 📈 Coverage Reports

### Generate Coverage

```bash
# HTML report (recommended)
make coverage
# Opens: tests/coverage/html/index.html

# Terminal report
make coverage-text

# Clover XML (for CI)
vendor/bin/phpunit --coverage-clover coverage.xml
```

### Coverage Goals

- ✅ Overall: **85%+**
- ✅ LayoutManager: **90%**
- ✅ CssManager: **85%**
- ✅ SettingsManager: **90%**

## 🧪 Test Structure

```
tests/
├── bootstrap.php                      # Bootstrap & setup
├── phpunit.xml                       # PHPUnit config
├── Helpers/
│   ├── TestCase.php                 # Base test case
│   └── WordPressMocks.php           # WP function mocks
├── Unit/                            # Unit tests
│   ├── LayoutManagerTest.php        # 13 tests ✓
│   ├── CssManagerTest.php           # 11 tests ✓
│   └── SettingsManagerTest.php      # 12 tests ✓
├── Integration/                     # Integration tests
│   └── LayoutSystemIntegrationTest.php  # 8 tests ✓
└── Feature/                         # Feature tests
    ├── ConfigLoadingTest.php        # 4 tests ✓
    └── LayoutRenderingTest.php      # 5 tests ✓
```

## ✍️ Writing Tests

### Test Template

```php
<?php
namespace Jankx\WooCommerce\Tests\Unit;

use Jankx\WooCommerce\Tests\Helpers\TestCase;

class MyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Setup test environment
    }

    /** @test */
    public function it_does_something_expected()
    {
        // Arrange
        $component = new MyComponent();
        
        // Act
        $result = $component->doSomething();
        
        // Assert
        $this->assertTrue($result);
    }

    protected function tearDown(): void
    {
        // Cleanup
        parent::tearDown();
    }
}
```

### Helper Methods

```php
// Access private properties
$value = $this->getPrivateProperty($object, 'propertyName');

// Set private properties
$this->setPrivateProperty($object, 'propertyName', $value);

// Call private methods
$result = $this->callPrivateMethod($object, 'methodName', [$arg1, $arg2]);

// Assert trait usage
$this->assertClassUsesTrait(TraitName::class, MyClass::class);

// Assert interface implementation
$this->assertClassImplementsInterface(InterfaceName::class, MyClass::class);
```

### WordPress Mocks

```php
// All WordPress functions are automatically mocked:

// Options
update_option('key', 'value');
$value = get_option('key', 'default');

// Transients
set_transient('key', 'value', 3600);
$value = get_transient('key');

// Hooks
add_action('hook', $callback);
do_action('hook', $arg);
add_filter('hook', $callback);
$value = apply_filters('hook', $value);

// Reset between tests (automatic in setUp)
WordPressMocks::reset();
```

## 🎯 Best Practices

### 1. Descriptive Test Names

```php
// ✅ Good
/** @test */
public function it_registers_layout_successfully()

// ❌ Bad
/** @test */
public function test1()
```

### 2. Arrange-Act-Assert Pattern

```php
/** @test */
public function it_calculates_total()
{
    // Arrange
    $calculator = new Calculator();
    
    // Act
    $result = $calculator->add(2, 3);
    
    // Assert
    $this->assertEquals(5, $result);
}
```

### 3. Test One Thing

```php
// ✅ Good - specific test
/** @test */
public function it_validates_email_format() { }

// ✅ Good - another specific test
/** @test */
public function it_rejects_empty_email() { }

// ❌ Bad - tests multiple things
/** @test */
public function it_validates_all_inputs() { }
```

### 4. Test Edge Cases

```php
/** @test */
public function it_handles_null_input() { }

/** @test */
public function it_handles_empty_array() { }

/** @test */
public function it_handles_negative_numbers() { }
```

## 🔍 Debugging Tests

### Verbose Output

```bash
vendor/bin/phpunit --verbose
vendor/bin/phpunit --testdox
```

### Debug Specific Test

```bash
vendor/bin/phpunit --filter it_registers_layout --debug
```

### Print Debug Info

```php
/** @test */
public function it_debugs_something()
{
    var_dump($variable);
    fwrite(STDERR, print_r($data, TRUE));
    $this->assertTrue(true);
}
```

## 🤖 Continuous Integration

Tests run automatically on GitHub Actions:

- ✅ PHP 7.4, 8.0, 8.1, 8.2
- ✅ Multiple dependency versions
- ✅ Code coverage uploaded to Codecov
- ✅ Code quality checks (PHPStan, PHPCS)

### Run CI Locally

```bash
make ci
```

## 🐛 Troubleshooting

### Issue: Class not found

```bash
composer dump-autoload
```

### Issue: Tests not running

```bash
# Check PHPUnit is installed
composer show phpunit/phpunit

# Reinstall dependencies
rm -rf vendor
composer install --dev
```

### Issue: Coverage not working

```bash
# Check Xdebug is installed
php -m | grep xdebug

# Enable coverage in php.ini
xdebug.mode=coverage
```

### Issue: WordPress functions undefined

```bash
# Check bootstrap.php loads WordPressMocks
cat tests/bootstrap.php | grep WordPressMocks
```

## 📚 Resources

- [PHPUnit Docs](https://phpunit.de/documentation.html)
- [Test-Driven Development](https://martinfowler.com/bliki/TestDrivenDevelopment.html)
- [WordPress Testing](https://make.wordpress.org/core/handbook/testing/)

## 🎖️ Test Coverage Badge

Add to README.md:

```markdown
[![Tests](https://github.com/user/repo/workflows/Tests/badge.svg)](https://github.com/user/repo/actions)
[![Coverage](https://codecov.io/gh/user/repo/branch/main/graph/badge.svg)](https://codecov.io/gh/user/repo)
```

---

**Happy Testing! 🧪 Keep coverage high and bugs low!**

