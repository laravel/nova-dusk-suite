<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/sleep', function (Request $request) {
    sleep(5);

    return $request->user();
});

Route::get('/', function (Request $request) {
    return 'Hello World';
});
