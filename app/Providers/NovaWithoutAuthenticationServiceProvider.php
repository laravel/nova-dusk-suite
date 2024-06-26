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
        Nova::routes()
            ->withoutAuthenticationRoutes()
            ->withPasswordResetRoutes()
            ->register(fortify: false);
    }
}
