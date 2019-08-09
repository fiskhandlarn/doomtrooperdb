[![Build Status](https://api.travis-ci.org/fiskhandlarn/doomtrooperdb.svg?branch=master)](https://travis-ci.org/fiskhandlarn/doomtrooperdb)

DoomtrooperDB
=======

# Very quick guide on how to install a local copy

This guide assumes you know how to use the command-line and that your machine has php and mysql installed.

- install composer: https://getcomposer.org/download/
- clone the repo somewhere
- cd to it
- run `composer install` (at the end it will ask for the database configuration parameters)
- run `php bin/console doctrine:database:create`
- run `php bin/console doctrine:migrations:migrate`
- run `php bin/console doctrine:fixtures:load --env=prod` to load default application data
- run `php bin/console app:import:std ../doomtrooperdb-json-data` or whatever the path to your DoomtrooperDB JSON data repository is
- run `php bin/console bazinga:js-translation:dump src/AppBundle/Resources/public/js` to generate translation JS
- run `npm install && npm run dev` to compile CSS and JS

## Setup an admin account

- register (or run `php bin/console fos:user:create <username>`)
- make sure your account is enabled (or run `php bin/console fos:user:activate <username>`)
- run `php bin/console fos:user:promote --super <username>`
