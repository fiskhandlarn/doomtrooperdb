# DoomtrooperDB
[![Build Status](https://api.travis-ci.org/fiskhandlarn/doomtrooperdb.svg?branch=master)](https://travis-ci.org/fiskhandlarn/doomtrooperdb)

## Install
- Run `composer install` (at the end it will ask for the database configuration parameters)
- Run `php bin/console doctrine:database:create`
- Run `php bin/console doctrine:migrations:migrate`
- Run `php bin/console doctrine:fixtures:load --env=prod` to load default application data
- Run `php bin/console app:import:std ../doomtrooperdb-json-data` or whatever the path to your [DoomtrooperDB JSON data repository](https://github.com/fiskhandlarn/doomtrooperdb-json-data) is
- Run `php bin/console app:import:images ../doomtrooperdb-json-data public/images`
- Run `php bin/console bazinga:js-translation:dump src/AppBundle/Resources/public/js` to generate translation JS
- Run `npm install && npm run dev` to compile CSS and JS

Or use `make`:
```bash
make install
make import
make build
```

## Docker

### Install via Docker

```bash
docker-compose run php sh -c "cd /home/wwwroot/sf4 && composer install"
docker-compose run php sh -c "cd /home/wwwroot/sf4 && php bin/console doctrine:database:create"
docker-compose run php sh -c "cd /home/wwwroot/sf4 && php bin/console doctrine:migrations:migrate"
docker-compose run php sh -c "cd /home/wwwroot/sf4 && php bin/console doctrine:fixtures:load --env=prod"
docker-compose run php sh -c "cd /home/wwwroot/sf4 && php bin/console app:import:std doomtrooperdb-json-data"
docker-compose run php sh -c "cd /home/wwwroot/sf4 && php bin/console app:import:images doomtrooperdb-json-data public/images"
docker-compose run php sh -c "cd /home/wwwroot/sf4 && php bin/console bazinga:js-translation:dump src/AppBundle/Resources/public/js"
npm install && npm run dev
```

Or use `make`:
```bash
make docker:install
make docker:import
make docker:build
```

### Start/stop Docker

Start Docker:
```bash
$ make up
```

or

```bash
$ docker-compose up -d
```

Access the site via [http://localhost/](http://localhost/).

Stop Docker:
```bash
$ make down
```

or

Stop Docker:
```bash
$ docker-compose down
```

## Setup an admin account

- Register (or run `php bin/console fos:user:create <username>`)
- Make sure your account is enabled (or run `php bin/console fos:user:activate <username>`)
- Run `php bin/console fos:user:promote --super <username>`
