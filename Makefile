.PHONY: all

all:
	@echo Please specify your command. Available commands: 'make test' or 'make validate'

test:
	php ./vendor/bin/phpunit

validate:
	./vendor/bin/phpstan analyze -l 9 src
	./vendor/bin/phpcs --standard="php_cs.xml" ./src/