.PHONY: test test-coverage test-coverage-html test-coverage-clover test-unit test-unit-coverage test-unit-coverage-html test-unit-coverage-clover install

test:
	@./vendor/bin/phpunit

test-coverage:
	@./vendor/bin/phpunit --coverage-text

test-coverage-html:
	@./vendor/bin/phpunit --coverage-html ./tests/report/html

test-coverage-clover:
	@./vendor/bin/phpunit --coverage-clover=./tests/report/coverage.clover

test-unit:
	@./vendor/bin/phpunit --testsuite unit

test-unit-coverage:
	@./vendor/bin/phpunit --testsuite unit --coverage-text

test-unit-coverage-html:
	@./vendor/bin/phpunit --testsuite unit --coverage-html ./tests/report/unit/html

test-unit-coverage-clover:
	@./vendor/bin/phpunit --testsuite unit --coverage-clover=./tests/report/unit/coverage.clover

install:
	@composer install
