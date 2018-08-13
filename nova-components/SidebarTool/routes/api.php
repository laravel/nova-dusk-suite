<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Otwell\SidebarTool\Http\Middleware\Authorize;

Route::middleware(['nova', Authorize::class])->group(function () {
    Route::get('/nova-api/custom-sidebar-tool', function (Request $request) {
        return 'Hello World';
    });
});
