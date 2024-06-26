<?php

namespace Laravel\Nova\Tests;

use Illuminate\Support\Arr;
use Inertia\ServiceProvider as InertiaServiceProvider;
use Laravel\Dusk\Browser;
use Laravel\Fortify\FortifyServiceProvider;
use Laravel\Nova\NovaCoreServiceProvider;
use Laravel\Nova\NovaServiceProvider;
use Laravel\Scout\ScoutServiceProvider;

use function Orchestra\Testbench\package_path;

abstract class DuskTestCase extends \Orchestra\Testbench\Dusk\TestCase
{
    use Concerns\DatabaseTruncation;

    /** {@inheritDoc} */
    protected static $baseServeHost = '127.0.0.1';

    /** {@inheritDoc} */
    protected static $baseServePort = 8085;

    /** {@inheritDoc} */
    protected $loadEnvironmentVariables = true;

    /** {@inheritDoc} */
    #[\Override]
    public static function applicationBasePath()
    {
        return package_path(['vendor', 'laravel', 'nova-dusk-suite']);
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        $this->afterApplicationCreated(function () {
            $this->withoutMockingConsoleOutput();
        });

        parent::setUp();
    }

    /** {@inheritDoc} */
    protected function setUpDuskServer(): void
    {
        parent::setUpDuskServer();

        tap($this->app->make('config'), function ($config) {
            $config->set('app.url', static::applicationBaseUrl());
            $config->set('filesystems.disks.public.url', static::applicationBaseUrl().'/storage');
        });
    }

    /** {@inheritDoc} */
    protected function getPackageProviders($app)
    {
        return [
            FortifyServiceProvider::class,
            InertiaServiceProvider::class,
            NovaCoreServiceProvider::class,
            NovaServiceProvider::class,
            ScoutServiceProvider::class,
        ];
    }

    /** {@inheritDoc} */
    protected function resolveApplicationResolvingCallback($app): void
    {
        parent::resolveApplicationResolvingCallback($app);

        $app->detectEnvironment(function () {
            return 'testing';
        });
    }

    /** {@inheritDoc} */
    protected function resolveApplicationConsoleKernel($app)
    {
        $app->singleton(
            'Illuminate\Contracts\Console\Kernel', class_exists('App\Console\Kernel') ? 'App\Console\Kernel' : 'Illuminate\Foundation\Console\Kernel'
        );
    }

    /** {@inheritDoc} */
    protected function resolveApplicationHttpKernel($app)
    {
        $app->singleton(
            'Illuminate\Contracts\Http\Kernel', class_exists('App\Http\Kernel') ? 'App\Http\Kernel' : 'Illuminate\Foundation\Http\Kernel'
        );
    }

    /** {@inheritDoc} */
    protected function resolveApplicationExceptionHandler($app)
    {
        $app->singleton(
            'Illuminate\Contracts\Debug\ExceptionHandler',
            class_exists('App\Exceptions\Handler') ? 'App\Exceptions\Handler' : 'Illuminate\Foundation\Exceptions\Handler'
        );
    }

    /**
     * Run the given callback with searchable functionality enabled.
     *
     * @return void
     */
    protected function whileSearchable(callable $callback)
    {
        $this->defineApplicationStates('searchable');

        call_user_func($callback);
    }

    /**
     * Run the given callback with inline-create functionality enabled.
     *
     * @return void
     */
    protected function whileInlineCreate(callable $callback)
    {
        $this->defineApplicationStates('inline-create');

        call_user_func($callback);
    }

    /**
     * Run the given callback with index-query-asc-order functionality enabled.
     *
     * @return void
     */
    protected function whileIndexQueryAscOrder(callable $callback)
    {
        $this->defineApplicationStates('index-query-asc-order');

        call_user_func($callback);
    }

    /**
     * Define application states.
     *
     * @param  array|string  $states
     * @return $this
     */
    protected function defineApplicationStates($states)
    {
        foreach (Arr::wrap($states) as $state) {
            touch(base_path(".{$state}"));
        }

        $this->beforeApplicationDestroyed(function () use ($states) {
            foreach (Arr::wrap($states) as $state) {
                @unlink(base_path(".{$state}"));
            }
        });

        return $this;
    }

    /**
     * Create a new Browser instance.
     *
     * @param  \Facebook\WebDriver\Remote\RemoteWebDriver  $driver
     * @return \Laravel\Dusk\Browser
     */
    protected function newBrowser($driver)
    {
        return tap(new Browser($driver), function ($browser) {
            $browser->fitOnFailure = false;

            $browser->resize(env('DUSK_WIDTH'), env('DUSK_HEIGHT'));
        });
    }
}
