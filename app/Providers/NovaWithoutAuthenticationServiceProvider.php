<?php

namespace App\Providers;

use Laravel\Nova\Nova;

class NovaWithoutAuthenticationServiceProvider extends NovaServiceProvider
{
    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes()
    {
        config([
            'nova.routes.login' => '/login',
            'nova.routes.logout' => '/logout',
        ]);

        Nova::routes()
            ->withoutAuthenticationRoutes()
            ->withPasswordResetRoutes()
            ->register(fortify: false);
    }
}
