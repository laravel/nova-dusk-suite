#!/bin/bash

composer create-project "laravel/laravel:10.x-dev" skeleton --no-scripts --no-plugins --quiet

cp -f ./skeleton/app/Console/Kernel.php ./app/Console
cp -f ./skeleton/app/Exceptions/Handler.php ./app/Exceptions
cp -f ./skeleton/app/Http/Kernel.php ./app/Http
cp -f ./skeleton/app/Http/Controllers/*.php ./app/Http/Controllers
cp -f ./skeleton/app/Http/Middleware/*.php ./app/Http/Middleware
cp -f ./skeleton/config/*.php ./config
rm ./config/sanctum.php
# cp -f ./skeleton/database/migrations/*.php ./database/migrations/
# cp -f ./skeleton/lang/en/*.php ./lang/en/

rm -Rf ./skeleton

rm -Rf ./lang/vendor/nova
rm -Rf ./public/vendor/nova
rm -Rf ./resources/lang/vendor/nova
rm -Rf ./resources/views/vendor/nova
rm -Rf ./tests/Browser/*
# cp -rf ./vendor/laravel/nova/tests/bootstrap.php ./tests/
cp -rf ./vendor/laravel/nova/tests/Browser/* ./tests/Browser/
# cp -rf ./vendor/laravel/nova/tests/DuskTestCase.php ./tests/
cp -rf ./vendor/laravel/nova/tests/Concerns/DatabaseTruncation.php ./tests/Concerns/
php artisan nova:publish --force
# php artisan tinker --execute="file_put_contents('config/nova.php', str_replace(\"env('NOVA_APP_NAME', env('APP_NAME')),\", \"'Nova Site',\", file_get_contents('config/nova.php')))"
