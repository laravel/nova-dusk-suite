<?php

namespace Otwell\CustomField;

use Illuminate\Support\Env;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class FieldServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Nova::serving(function (ServingNova $event) {
            if (Env::get('DUSK_REMOTE_ASSETS') === true) {
                Nova::remoteScript(mix('field.js', 'vendor/nova-components/custom-field'));
            } else {
                Nova::script('custom-field', __DIR__.'/../dist/js/field.js');
                // Nova::style('custom-field', __DIR__.'/../dist/css/field.css');
            }
        });

        if (Env::get('DUSK_REMOTE_ASSETS') === true) {
            $this->publishes([
                __DIR__.'/../dist' => public_path('vendor/nova-components/custom-field'),
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
