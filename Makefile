install:
	composer install
	php bin/console bazinga:js-translation:dump
	php bin/console assets:install --symlink web

test:
	vendor/bin/phpunit
