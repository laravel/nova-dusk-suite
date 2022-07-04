<?php

namespace App\Providers;

use App\Nova\Dashboards\Main;
use App\Nova\Dashboards\Posts;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Menu\Menu;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Menu\MenuSection;
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

        // Nova::remoteStyle(mix('css/nova.css'));
        // Nova::remoteScript(mix('js/nova.js'));

        Nova::mainMenu(function (Request $request, Menu $menu) {
            if ($user = $request->user()) {
                $menu->append(
                    MenuSection::make('Account Verification', [
                        MenuItem::externalLink('Verify Using Inertia', "/tests/verify-user/{$user->id}")->method('POST', ['_token' => csrf_token()], []),
                    ])->canSee(function () use ($user) {
                        return ! $user->active;
                    })
                )->append(
                    MenuSection::make('Links', [
                        MenuItem::externalLink('Dashboard', url('/dashboard')),
                        MenuItem::externalLink('Nova Website', 'https://nova.laravel.com'),
                    ])
                );
            }

            return $menu;
        });

        Nova::userMenu(function (Request $request, Menu $menu) {
            if ($user = $request->user()) {
                $menu->append(
                    MenuItem::make('My Account')->path('/resources/users/'.$request->user()->id)
                )->append(
                    MenuItem::externalLink('Verify Account', "/tests/verify-user/{$user->id}")->method('POST', ['_token' => csrf_token()])
                        ->canSee(function () use ($user) {
                            return ! $user->active;
                        })
                )->append(
                    MenuItem::externalLink('Dashboard', route('dashboard'))
                );
            }

            return $menu;
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
                return ! (optional($request->user())->isBlockedFrom('sidebarTool') || false);
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
        Nova::notificationPollingInterval(CarbonInterval::days(1)->totalSeconds);

        Nova::serving(function (ServingNova $event) {
            if (! is_null($pagination = data_get($event->request->user(), 'settings.pagination'))) {
                config(['nova.pagination' => $pagination]);
            }
        });

        Nova::userTimezone(function (Request $request) {
            /** @param string|null $default */
            $default = config('app.timezone');

            return $request->user()->profile->timezone ?? $default;
        });
    }
}
