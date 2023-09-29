<?php

namespace Otwell\ResourceTool;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Nova::serving(function (ServingNova $event) {
            Nova::script('resource-tool', __DIR__.'/../dist/js/tool.js');
        });

        Nova::serving(function (ServingNova $event) {
            if (Env::get('DUSK_REMOTE_ASSETS')) {
                Nova::remoteScript(mix('tool.js', 'vendor/nova-components/resource-tool'));
            } else {
                Nova::script('resource-tool', __DIR__.'/../dist/js/tool.js');
            }
        });

        if (Env::get('DUSK_REMOTE_ASSETS')) {
            $this->publishes([
                __DIR__.'/../dist' => public_path('vendor/nova-components/resource-tool'),
            ], ['nova-assets', 'laravel-assets']);
        }
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
