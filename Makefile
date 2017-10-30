all: install

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

lint-php-ci:
	./vendor/bin/phpcs --report-checkstyle=phpcs-checkstyle.xml --report-full

lint-twig:
	tests/console lint:twig src tests

lint-yaml:
	tests/console lint:yaml src
	tests/console lint:yaml tests

lint-xliff:
	tests/console lint:xliff src
	tests/console lint:xliff tests

stan:
	./vendor/bin/phpstan analyse -c phpstan.neon -l 5 src tests

stan-ci:
	./vendor/bin/phpstan --no-interaction --no-progress analyse --errorFormat=checkstyle -c phpstan.neon -l 5 src tests > phpstan-checkstyle.xml

test: test-phpunit test-behat

test-phpunit: var/cache var/logs var/translations
	./vendor/bin/phpunit --configuration ./phpunit.xml

test-phpunit-ci: var/cache var/logs var/translations
	chmod -R 777 ./var/logs
	php -dxdebug.coverage_enable=1 ./vendor/bin/phpunit --configuration ./phpunit.xml --log-junit ./phpunit-result.xml --coverage-clover ./clover.xml

var/cache:
	mkdir -p var/cache

var/logs:
	mkdir -p var/logs

var/translations:
	mkdir -p var/translations

.PHONY: all install install-ci lint lint-ci lint-php lint-php-ci lint-yaml lint-twig lint-xliff stan stan-ci test test-phpunit test-phpunit-ci
