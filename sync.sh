#!/bin/bash

rm -Rf ./public/vendor/nova
rm -Rf ./resources/lang/vendor/nova
rm -Rf ./resources/views/vendor/nova
rm -Rf ./tests/Browser/*
cp -rf ./vendor/laravel/nova/tests/Browser/* ./tests/Browser/
# cp -rf ./vendor/laravel/nova/tests/DuskTestCase.php ./tests/
php artisan nova:publish
