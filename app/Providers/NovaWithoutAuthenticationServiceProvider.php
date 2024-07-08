<?php

namespace App\Providers;

use Laravel\Nova\Nova;

class NovaWithoutAuthenticationServiceProvider extends NovaServiceProvider
{
    /**
     * Register the Fortify configurations.
     *
     * @return void
     */
    protected function fortify()
    {
        Nova::fortify()
            ->register(routes: false);
    }

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes()
    {
        Nova::routes()
            ->withoutAuthenticationRoutes()
            ->withPasswordResetRoutes();
    }
}
