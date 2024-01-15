<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    App\Providers\NovaServiceProvider::class,

    Laravel\Dusk\DuskServiceProvider::class,
    Otwell\CustomField\FieldServiceProvider::class,
    Otwell\IconsViewer\ToolServiceProvider::class,
    Otwell\RememberTokenCopier\AssetServiceProvider::class,
    Otwell\ResourceTool\ToolServiceProvider::class,
    Otwell\SidebarTool\ToolServiceProvider::class,
];
