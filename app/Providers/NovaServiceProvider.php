<?php

namespace App\Providers;

use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\ServiceProvider;

class NovaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Nova::routes()
                ->withAuthenticationRoutes()
                ->withPasswordResetRoutes();

        Nova::serving(function (ServingNova $event) {
            Nova::resourcesIn(app_path('Nova'));

            Nova::cards([]);
            Nova::tools([]);
        });
    }
}
