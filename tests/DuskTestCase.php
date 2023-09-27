<?php

namespace Laravel\Nova\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;
use Laravel\Dusk\Browser;
use Orchestra\Testbench\Dusk\Foundation\PackageManifest;

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
     *
     * @return void
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
     *
     * @return void
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

    /**
     * Get application aliases.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getApplicationAliases($app)
    {
        return $app['config']['app.aliases'];
    }

    /**
     * Get application providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getApplicationProviders($app)
    {
        return $app['config']['app.providers'];
    }

    /**
     * Resolve application implementation.
     *
     * @return \Illuminate\Foundation\Application
     */
    protected function resolveApplication()
    {
        return tap(new Application($this->getBasePath()), function ($app) {
            $app->detectEnvironment(function () {
                return 'testing';
            });

            PackageManifest::swap($app, $this);
        });
    }

    /**
     * Resolve application core configuration implementation.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function resolveApplicationConfiguration($app)
    {
        $app->make('Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables')->bootstrap($app);

        parent::resolveApplicationConfiguration($app);
    }

    /**
     * Resolve application Console Kernel implementation.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function resolveApplicationConsoleKernel($app)
    {
        $app->singleton('Illuminate\Contracts\Console\Kernel', 'App\Console\Kernel');
    }

    /**
     * Resolve application HTTP Kernel implementation.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function resolveApplicationHttpKernel($app)
    {
        $app->singleton('Illuminate\Contracts\Http\Kernel', 'App\Http\Kernel');
    }

    /**
     * Resolve application HTTP exception handler.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function resolveApplicationExceptionHandler($app)
    {
        $app->singleton('Illuminate\Contracts\Debug\ExceptionHandler', 'App\Exceptions\Handler');
    }

    /**
     * Run the given callback with searchable functionality enabled.
     *
     * @param  callable  $callback
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
     * @param  callable  $callback
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
     * @param  callable  $callback
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
