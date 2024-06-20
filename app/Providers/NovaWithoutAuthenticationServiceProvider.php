<?php

namespace App\Providers;

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
            ->withoutAuthenticationRoutes(fortify: false)
            ->withPasswordResetRoutes();
    }
}
