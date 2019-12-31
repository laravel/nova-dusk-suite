<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Cards\Help;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Otwell\SidebarTool\SidebarTool;

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

            Nova::tools([(new SidebarTool)->canSee(function ($request) {
                return ! $request->user()->isBlockedFrom('sidebarTool');
            })]);
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
