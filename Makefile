install:
	composer install
	composer run symfony-scripts
	npm install
	php bin/console doctrine:database:create
	php bin/console doctrine:migrations:migrate
	php bin/console doctrine:fixtures:load --env=prod

import:
	php bin/console app:import:std ../doomtrooperdb-json-data
	php bin/console app:import:images ../doomtrooperdb-json-data public/images

build:
	php bin/console bazinga:js-translation:dump src/AppBundle/Resources/public/js
	npm run dev

docker_install:
	composer install
	docker-compose run php sh -c "cd /home/wwwroot/sf4 && composer run symfony-scripts"
	npm install
	docker-compose run php sh -c "cd /home/wwwroot/sf4 && php bin/console doctrine:database:create"
	docker-compose run php sh -c "cd /home/wwwroot/sf4 && php bin/console doctrine:migrations:migrate"
	docker-compose run php sh -c "cd /home/wwwroot/sf4 && php bin/console doctrine:fixtures:load --env=prod"

docker_import:
	docker-compose run php sh -c "cd /home/wwwroot/sf4 && php bin/console app:import:std doomtrooperdb-json-data"
	docker-compose run php sh -c "cd /home/wwwroot/sf4 && php bin/console app:import:images doomtrooperdb-json-data public/images"

docker_build:
	docker-compose run php sh -c "cd /home/wwwroot/sf4 && php bin/console bazinga:js-translation:dump src/AppBundle/Resources/public/js"
	npm run dev

up:
	docker-compose up -d

down:
	docker-compose down --remove-orphans

test:
	vendor/bin/phpcs --ignore=src/migrations src tests app
	php bin/console lint:yaml app/config
	php bin/console lint:twig src
	php bin/console security:check
	php bin/console doctrine:schema:validate --skip-sync --skip-mapping -vvv --no-interaction
	vendor/bin/phpunit

docker_test:
	docker-compose run php sh -c "cd /home/wwwroot/sf4 && vendor/bin/phpcs --ignore=src/migrations src tests app"
	docker-compose run php sh -c "cd /home/wwwroot/sf4 && php bin/console lint:yaml app/config"
	docker-compose run php sh -c "cd /home/wwwroot/sf4 && php bin/console lint:twig src"
	docker-compose run php sh -c "cd /home/wwwroot/sf4 && php bin/console security:check"
	docker-compose run php sh -c "cd /home/wwwroot/sf4 && php bin/console doctrine:schema:validate --skip-sync --skip-mapping -vvv --no-interaction"
	docker-compose run php sh -c "cd /home/wwwroot/sf4 && vendor/bin/phpunit"
