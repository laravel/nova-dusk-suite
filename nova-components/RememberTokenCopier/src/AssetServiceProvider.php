<?php

namespace Otwell\RememberTokenCopier;

use Illuminate\Support\Env;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class AssetServiceProvider extends ServiceProvider
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
                Nova::remoteScript(mix('js/asset.js', 'vendor/nova-components/remember-token-copier'));
            } else {
                Nova::script('remember-token-copier', __DIR__.'/../dist/js/asset.js');
            }
        });

        if (Env::get('DUSK_REMOTE_ASSETS') === true) {
            $this->publishes([
                __DIR__.'/../dist' => public_path('vendor/nova-components/remember-token-copier'),
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
