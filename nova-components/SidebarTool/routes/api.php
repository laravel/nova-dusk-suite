<?php

<<<<<<< HEAD
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Otwell\SidebarTool\Http\Middleware\Authorize;
=======
>>>>>>> 7fea1501482f448cc8cc5a4eeac2c908f072d07d

Route::middleware(['nova', Authorize::class])->group(function () {
    Route::get('/nova-api/custom-sidebar-tool', function (Request $request) {
        return 'Hello World';
    });
});
