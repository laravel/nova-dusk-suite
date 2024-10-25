<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SuiteServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->instance('uses_searchable', file_exists(base_path('.searchable')));
        $this->app->instance('uses_inline_create', file_exists(base_path('.inline-create')));
        $this->app->instance('uses_with_reordering', ! file_exists(base_path('.disable-reordering')));
        $this->app->instance('uses_breadcrumbs', ! file_exists(base_path('.disable-breadcrumbs')));
        $this->app->instance('uses_without_trashed', file_exists(base_path('.without-trashed')));
    }
}
