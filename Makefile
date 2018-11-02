.PHONY: test test-coverage test-coverage-html test-coverage-clover test-unit test-unit-coverage test-unit-coverage-html test-unit-coverage-clover install

# Setting up

setup: ## Install dependencies and set up example conf file
	@docker-compose run --rm composer install

# Testing

test: ## Run all tests
test: test-coverage test-unit

# Coverage tests
test-coverage: ## Run coverage tests
	@docker-compose run --rm php-55 ./vendor/bin/phpunit --coverage-text

test-coverage-html:
	@docker-compose run --rm php-55 ./vendor/bin/phpunit --coverage-html ./tests/report/html

test-coverage-clover:
	@docker-compose run --rm php-55 ./vendor/bin/phpunit --coverage-clover=./tests/report/coverage.clover

# Unit tests
test-unit: ## Run unit tests
	@docker-compose run --rm php-55 ./vendor/bin/phpunit --testsuite unit

# Code sniffer
lint: ## Run phpcs against the code.
	@docker-compose run --rm php-55 vendor/bin/phpcs -p --warning-severity=0 src/ tests/

lint-fix: ## Run phpcbf against the code.
	@docker-compose run --rm php-55 vendor/bin/phpcbf -p src/

.SILENT: help
help: ## Show this help message
	set -x
	echo "Usage: make [target] ..."
	echo ""
	echo "Available targets:"
	egrep '^(.+)\:\ ##\ (.+)' ${MAKEFILE_LIST} | column -t -c 2 -s ':#'
