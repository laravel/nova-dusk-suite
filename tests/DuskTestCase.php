<?php

namespace Laravel\Nova\Tests;

use Illuminate\Support\Arr;
use Laravel\Dusk\Browser;

abstract class DuskTestCase extends \Orchestra\Testbench\Dusk\TestCase
{
    use Concerns\DatabaseTruncation;

    /**
     * The base serve host URL to use while testing the application.
     *
     * @var string
     */
    protected static $baseServeHost = '127.0.0.1';

    /**
     * The base serve port to use while testing the application.
     *
     * @var int
     */
    protected static $baseServePort = 8085;

    /**
     * Automatically loads environment file if available.
     *
     * @var bool
     */
    protected $loadEnvironmentVariables = true;

    /**
     * Get Application's base path.
     *
     * @return string
     */
    public static function applicationBasePath()
    {
        return realpath(__DIR__.'/../');
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

    /**
     * Server specific setup. It may share alot with the main setUp() method, but
     * should exclude things like DB migrations so we don't end up wiping the
     * DB content mid test. Using this method means we can be explicit.
     */
    protected function setUpDuskServer(): void
    {
        parent::setUpDuskServer();

        tap($this->app->make('config'), function ($config) {
            $config->set('app.url', static::applicationBaseUrl());
            $config->set('filesystems.disks.public.url', static::applicationBaseUrl().'/storage');
        });
    }

    /**
     * Reload serving on a given host and port.
     */
    public static function reloadServing(): void
    {
        static::stopServing();
        static::serve(static::$baseServeHost, static::$baseServePort);
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            'Inertia\ServiceProvider',
            'Laravel\Nova\NovaCoreServiceProvider',
            'Carbon\Laravel\ServiceProvider',
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

    /**
     * Resolve application Console Kernel implementation.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function resolveApplicationConsoleKernel($app)
    {
        $app->singleton(
            'Illuminate\Contracts\Console\Kernel', class_exists('App\Console\Kernel') ? 'App\Console\Kernel' : 'Illuminate\Foundation\Console\Kernel'
        );
    }

    /**
     * Resolve application HTTP Kernel implementation.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function resolveApplicationHttpKernel($app)
    {
        $app->singleton(
            'Illuminate\Contracts\Http\Kernel', class_exists('App\Http\Kernel') ? 'App\Http\Kernel' : 'Illuminate\Foundation\Http\Kernel'
        );
    }

    /**
     * Resolve application HTTP exception handler.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
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
     * @param  \Closure(\Laravel\Dusk\Browser):void  $callback
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
