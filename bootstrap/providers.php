<?php

return [
    App\Providers\AppServiceProvider::class,

    Laravel\Nova\NovaServiceProvider::class,
    Laravel\Dusk\DuskServiceProvider::class,
    Otwell\CustomField\FieldServiceProvider::class,
    Otwell\IconsViewer\ToolServiceProvider::class,
    Otwell\RememberTokenCopier\AssetServiceProvider::class,
    Otwell\ResourceTool\ToolServiceProvider::class,
    Otwell\SidebarTool\ToolServiceProvider::class,
];
