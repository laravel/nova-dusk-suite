<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    App\Providers\NovaServiceProvider::class,
    // App\Providers\RouteServiceProvider::class,
    App\Providers\SuiteServiceProvider::class,

    Laravel\Dusk\DuskServiceProvider::class,
    NovaComponents\CustomField\FieldServiceProvider::class,
    NovaComponents\IconsViewer\ToolServiceProvider::class,
    NovaComponents\RememberTokenCopier\AssetServiceProvider::class,
    NovaComponents\ResourceTool\ToolServiceProvider::class,
    NovaComponents\SidebarTool\ToolServiceProvider::class,
];
