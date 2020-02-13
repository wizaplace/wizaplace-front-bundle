all: install

build:
	mkdir -p build

install: var/translations var/cache var/logs
	composer install
	tests/console --env=test cache:clear

install-ci:
	composer install --no-interaction --no-progress --ignore-platform-reqs
	tests/console --env=test cache:clear --no-interaction

lint: lint-php lint-twig lint-yaml lint-xliff

lint-ci: lint-php-ci lint-twig lint-yaml lint-xliff

lint-php:
	./vendor/bin/phpcs

lint-php-ci: build
	./vendor/bin/phpcs --report-checkstyle=build/phpcs-checkstyle.xml --report-full

lint-twig:
	tests/console lint:twig src tests

lint-yaml:
	tests/console lint:yaml src
	tests/console lint:yaml tests

lint-xliff:
	tests/console lint:xliff src
	tests/console lint:xliff tests

stan:
	./vendor/bin/phpstan analyse -c phpstan.neon -l 5 src

stan-ci: build
	./vendor/bin/phpstan --no-interaction --no-progress analyse --error-format=checkstyle -c phpstan.neon -l 5 src > build/phpstan-checkstyle.xml

test: test-phpunit

test-phpunit: var/cache var/logs var/translations
	./vendor/bin/phpunit --configuration ./phpunit.xml

test-phpunit-ci: var/cache var/logs var/translations build
	chmod -R 777 ./var/logs
	php -dxdebug.coverage_enable=1 ./vendor/bin/phpunit --configuration ./phpunit.xml --log-junit build/junit.xml --coverage-clover ./clover.xml

delete-all-cassettes:
	cd tests/ && find . -name "*.yml" -type f -delete

var/cache:
	mkdir -p var/cache

var/logs:
	mkdir -p var/logs

var/translations:
	mkdir -p var/translations

.PHONY: all install install-ci lint lint-ci lint-php lint-php-ci lint-yaml lint-twig lint-xliff stan stan-ci test test-phpunit test-phpunit-ci
