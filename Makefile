.PHONY: vendor


vendor: composer.json composer.lock
	composer self-update
	composer validate
	composer install