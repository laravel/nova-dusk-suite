<?php

namespace App\Providers;

use Laravel\Nova\Nova;
use Laravel\Nova\Cards\Help;
use Illuminate\Support\Facades\Gate;
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
        Nova::routes()
                ->withAuthenticationRoutes()
                ->withPasswordResetRoutes();

        Nova::serving(function (ServingNova $event) {
            $this->authorization();

            Nova::resourcesIn(app_path('Nova'));
            Nova::cards([new Help]);
            Nova::tools([]);
        });
    }

    /**
     * Configure the Nova authorization services.
     *
     * @return void
     */
    protected function authorization()
    {
        Gate::define('nova', function ($user) {
            return in_array($user->email, [
                //
            ]);
        });

        Nova::auth(function ($request) {
            return app()->environment('local') || Gate::check('nova');
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
