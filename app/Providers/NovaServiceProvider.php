<?php

namespace App\Providers;

use App\Models\User;
use App\Nova\Dashboards\Main;
use App\Nova\Dashboards\Posts;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Events\StartedImpersonating;
use Laravel\Nova\Events\StoppedImpersonating;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Menu\Menu;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;
use Laravel\Nova\Util;
use Otwell\IconsViewer\IconsViewer;
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
        Nova::remoteScript(mix('js/nova.js'));

        Nova::withBreadcrumbs(function ($request) {
            return $this->app->make('uses_breadcrumbs');
        });

        $this->registerCustomUserCommand();
        $this->registerImpersonatingEvents();
        $this->registerMainMenu();
        $this->registerUserMenu();
        $this->registerFieldMacros();
    }

    /**
     * Register custom `nova:user` command.
     *
     * @return void
     */
    protected function registerCustomUserCommand()
    {
        Nova::createUserUsing(
            function ($command) {
                /** @var \Illuminate\Console\Command $command */
                return [
                    $command->ask('Name'),
                    $command->ask('Email Address'),
                    $command->secret('Password'),
                    $command->confirm('Active', false),
                ];
            },
            function (string $name, string $email, string $password, bool $active) {
                $model = Util::userModel();

                return tap((new $model())->forceFill([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'active' => $active,
                ]))->save();
            }
        );
    }

    /**
     * Register impersonating events.
     *
     * @return void
     */
    protected function registerImpersonatingEvents()
    {
        Event::listen(StartedImpersonating::class, function ($event) {
            config([
                'nova.impersonation.started' => '/?'.http_build_query([
                    'impersonated' => $event->impersonated->getKey(),
                    'impersonator' => $event->impersonator->getKey(),
                ]),
            ]);
        });

        Event::listen(StoppedImpersonating::class, function ($event) {
            /** @var class-string<\Laravel\Nova\Resource> $resource */
            $resource = Nova::resourceForModel($event->impersonated);

            config([
                'nova.impersonation.stopped' => route('nova.pages.detail', [
                    'resource' => $resource::uriKey(),
                    'resourceId' => $event->impersonated->getKey(),
                ]),
            ]);
        });
    }

    /**
     * Register main menu.
     *
     * @return void
     */
    protected function registerMainMenu()
    {
        Nova::mainMenu(function (Request $request, Menu $menu) {
            transform($request->user(), function ($user) use ($menu) {
                /** @var \App\Models\User $user */
                $menu->append(
                    MenuSection::make('Account Verification', [
                        MenuItem::externalLink('Verify Using Inertia', "/tests/verify-user/{$user->id}")->method('POST', ['_token' => csrf_token()], []),
                    ])->canSee(function () use ($user) {
                        return ! $user->active;
                    })
                )->append(
                    MenuSection::make('Links', [
                        MenuItem::externalLink('Dashboard', url('/dashboard')),
                        MenuItem::externalLink('Nova Website', 'https://nova.laravel.com')->openInNewTab(),
                    ])
                );
            });

            return $menu;
        });
    }

    /**
     * Register user menu.
     *
     * @return void
     */
    protected function registerUserMenu()
    {
        Nova::userMenu(function (Request $request, Menu $menu) {
            transform($request->user(), function ($user) use ($menu) {
                /** @var \App\Models\User $user */
                $menu->append(
                    MenuItem::make('My Account')->path('/resources/users/'.$user->id)
                )->append(
                    MenuItem::externalLink('Verify Account', "/tests/verify-user/{$user->id}")->method('POST', ['_token' => csrf_token()])
                        ->canSee(function () use ($user) {
                            return ! $user->active;
                        })
                )->append(
                    MenuItem::externalLink('Dashboard', route('dashboard'))
                );
            });

            return $menu;
        });
    }

    /**
     * Register field macros.
     *
     * @return void
     */
    public function registerFieldMacros()
    {
        Field::macro('showWhen', function (bool $condition) {
            /** @phpstan-ignore-next-line */
            return $condition === true ? $this->show() : $this->hide();
        });

        Field::macro('showUnless', function (bool $condition) {
            /** @phpstan-ignore-next-line */
            return $condition === true ? $this->hide() : $this->show();
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
            new IconsViewer,
            SidebarTool::make()->canSee(function (Request $request) {
                return ! transform($request->user(), function ($user) {
                    /** @var \App\Models\User|\App\Models\Subscriber $user */
                    return $user instanceof User && $user->isBlockedFrom('sidebarTool');
                }, false);
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
        Nova::notificationPollingInterval((int) CarbonInterval::days(1)->totalSeconds);

        Nova::serving(function (ServingNova $event) {
            if (! is_null($pagination = data_get($event->request->user(), 'settings.pagination'))) {
                config(['nova.pagination' => $pagination]);
            }
        });

        Nova::userTimezone(function (Request $request) {
            /** @param  string|null  $default */
            $default = config('app.timezone');

            return $request->user()->profile->timezone ?? $default;
        });
    }
}
