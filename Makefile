.PHONY: test coverage install help

# Default target
.DEFAULT_GOAL := help

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-20s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

install: ## Install dependencies
	composer install

test: ## Run all tests
	vendor/bin/phpunit

test-unit: ## Run unit tests only
	vendor/bin/phpunit --testsuite "Unit Tests"

test-integration: ## Run integration tests only
	vendor/bin/phpunit --testsuite "Integration Tests"

test-feature: ## Run feature tests only
	vendor/bin/phpunit --testsuite "Feature Tests"

test-filter: ## Run specific test (use FILTER=test_name)
	vendor/bin/phpunit --filter $(FILTER)

coverage: ## Generate coverage report
	vendor/bin/phpunit --coverage-html tests/coverage/html
	@echo "Coverage report generated at tests/coverage/html/index.html"

coverage-text: ## Show coverage in terminal
	vendor/bin/phpunit --coverage-text

watch: ## Watch and run tests on file changes (requires entr)
	find includes tests -name '*.php' | entr -c make test

clean: ## Clean coverage and cache files
	rm -rf tests/coverage
	rm -rf tests/logs
	rm -rf .phpunit.result.cache

autoload: ## Regenerate autoloader
	composer dump-autoload

lint: ## Run PHP linter
	find includes -name "*.php" -exec php -l {} \;

format: ## Format code (requires php-cs-fixer)
	vendor/bin/php-cs-fixer fix includes/

stan: ## Run PHPStan analysis
	vendor/bin/phpstan analyse --memory-limit=2G

quality: lint stan ## Run all code quality checks

ci: install test coverage ## Run CI pipeline locally

.PHONY: all
all: install test coverage ## Install, test and generate coverage

