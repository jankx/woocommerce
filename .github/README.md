# GitHub Workflows

## Tests Workflow

Automated testing pipeline chạy trên GitHub Actions.

### Trigger

- Push to `main` or `develop` branch
- Pull requests to `main` or `develop`

### Matrix Testing

Tests chạy trên multiple configurations:

**PHP Versions:**
- PHP 7.4
- PHP 8.0
- PHP 8.1
- PHP 8.2

**Dependency Versions:**
- prefer-lowest
- prefer-stable

Total: **8 test configurations** (4 PHP × 2 dependency versions)

### Jobs

#### 1. Test Job
- Install dependencies
- Run PHPUnit test suite
- Generate coverage report
- Upload to Codecov

#### 2. Code Quality Job
- Run PHPStan (static analysis)
- Run PHPCS (coding standards)

### Status Badges

Add to your README.md:

```markdown
[![Tests](https://github.com/username/jankx-woocommerce/workflows/Tests/badge.svg)](https://github.com/username/jankx-woocommerce/actions)
[![PHP Versions](https://img.shields.io/badge/php-7.4%20%7C%208.0%20%7C%208.1%20%7C%208.2-blue.svg)](https://github.com/username/jankx-woocommerce)
[![Coverage](https://codecov.io/gh/username/jankx-woocommerce/branch/main/graph/badge.svg)](https://codecov.io/gh/username/jankx-woocommerce)
```

### Local CI Testing

Run the same pipeline locally:

```bash
make ci
```

This will:
1. Install dependencies
2. Run all tests
3. Generate coverage report

---

**Continuous Integration ensures code quality on every commit! ✅**

