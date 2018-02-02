.PHONY: test test-coverage test-coverage-html test-coverage-clover test-unit test-unit-coverage test-unit-coverage-html test-unit-coverage-clover install

# Setting up

setup: ## Install dependencies and set up example conf file
	@docker-compose run --rm composer install

# Testing

test: ## Run all tests
test: test-coverage test-coverage-html test-coverage-clover test-unit test-unit-coverage test-unit-coverage-html test-unit-coverage-clover

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

test-unit-coverage:
	@docker-compose run --rm php-55 ./vendor/bin/phpunit --testsuite unit --coverage-text

test-unit-coverage-html:
	@docker-compose run --rm php-55 ./vendor/bin/phpunit --testsuite unit --coverage-html ./tests/report/unit/html

test-unit-coverage-clover:
	@docker-compose run --rm php-55 ./vendor/bin/phpunit --testsuite unit --coverage-clover=./tests/report/unit/coverage.clover

.SILENT: help
help: ## Show this help message
	set -x
	echo "Usage: make [target] ..."
	echo ""
	echo "Available targets:"
	egrep '^(.+)\:\ ##\ (.+)' ${MAKEFILE_LIST} | column -t -c 2 -s ':#'
