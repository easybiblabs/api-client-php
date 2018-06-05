.PHONY: all

all: ci

install:
	./composer.phar install --no-interaction --no-suggest

composer-validate:
	./composer.phar validate

phpunit:
	./vendor/bin/phpunit

ci:
	$(MAKE) composer-validate
	$(MAKE) phpunit
	./vendor/bin/phpcs --standard=psr2 ./src
	./vendor/bin/phpcs --standard=psr2 ./tests
	./vendor/bin/phpmd src/ text codesize,controversial,design,naming,unusedcode
	./vendor/bin/phpmd tests/ text codesize,controversial,design,naming,unusedcode
	./vendor/bin/php-cs-fixer --dry-run --verbose --diff fix src --fixers=unused_use
	./vendor/bin/php-cs-fixer --dry-run --verbose --diff fix tests --fixers=unused_use

docker-build:
	docker build -t api-client-php .

docker-run:
	docker run -it --rm --name api-client-php-test api-client-php

docker-run-test: docker-build docker-run
