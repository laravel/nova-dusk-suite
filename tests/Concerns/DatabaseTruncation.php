<?php

namespace Laravel\Nova\Tests\Concerns;

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
