#!/bin/bash

rm -Rf ./public/vendor/nova
rm -Rf ./resources/lang/vendor/nova
rm -Rf ./resources/views/vendor/nova
rm -Rf ./tests/Browser/*
cp -rf ./vendor/laravel/nova/tests/Browser/* ./tests/Browser/
# cp -rf ./vendor/laravel/nova/tests/DuskTestCase.php ./tests/
php artisan nova:publish --force
# php artisan tinker --execute="file_put_contents('config/nova.php', str_replace(\"env('NOVA_APP_NAME', env('APP_NAME')),\", \"'Nova Site',\", file_get_contents('config/nova.php')))"
