<?php

namespace Otwell\SidebarTool;

use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\ServiceProvider;

class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'sidebar-tool');

        Nova::serving(function (ServingNova $event) {
            Nova::script('sidebar-tool', __DIR__.'/../dist/js/tool.js');
            Nova::style('sidebar-tool', __DIR__.'/../dist/css/tool.css');
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
