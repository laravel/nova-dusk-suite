<?php

namespace Laravel\Nova\Tests\Concerns;

use Illuminate\Foundation\Application;

if (version_compare(Application::VERSION, '9.50', '<')) {
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
} else {
    trait DatabaseTruncation
    {
        use \Illuminate\Foundation\Testing\DatabaseTruncation;

        protected $seed = true;
    }
}
