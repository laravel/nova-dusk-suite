<?php

namespace Laravel\Nova\Tests\Concerns;

use Illuminate\Foundation\Application;

trait DatabaseTruncation
{
    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        $this->artisan('migrate:fresh', [
            '--seed' => true,
        ]);
    }
}
