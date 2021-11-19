<?php

namespace App\Providers;

use App\Nova\Dashboards\Main;
use App\Nova\Dashboards\Posts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use Otwell\SidebarTool\SidebarTool;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Nova::userMenu(function (Request $request) {
            return [
                MenuItem::make('My Account')->path('/resources/users/'.$request->user()->id),
            ];
        });
    }

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes()
    {
        Nova::routes()
                ->withAuthenticationRoutes()
                ->withPasswordResetRoutes()
                ->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewNova', function ($user) {
            return true;
        });
    }

    /**
     * Get the extra dashboards that should be displayed on the Nova dashboard.
     *
     * @return array
     */
    protected function dashboards()
    {
        return [
            new Main,
            new Posts,
        ];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools()
    {
        return [
            (new SidebarTool)->canSee(function (Request $request) {
                return ! $request->user()->isBlockedFrom('sidebarTool');
            }),
        ];
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Nova::serving(function (ServingNova $event) {
            if (! is_null($pagination = data_get($event->request->user(), 'settings.pagination'))) {
                config(['nova.pagination' => $pagination]);
            }
        });

        Nova::userTimezone(function (Request $request) {
            $default = config('app.timezone');

            return transform($request->user(), function ($user) use ($default) {
                return $user->profile->timezone ?? $default;
            }, $default);
        });
    }
}
