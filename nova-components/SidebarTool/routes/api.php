<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/nova-api/custom-sidebar-tool', function (Request $request) {
    return 'Hello World';
});
