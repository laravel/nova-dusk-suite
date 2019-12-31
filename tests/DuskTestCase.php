<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverDimension;
use Laravel\Dusk\TestCase as BaseTestCase;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare()
    {
        static::startChromeDriver();
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        $options = (new ChromeOptions)->addArguments([
            '--disable-gpu',
            // '--headless'
        ]);

        $driver = RemoteWebDriver::create(
            'http://localhost:9515', DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );

        $driver->manage()->window()->maximize();

        if (env('DUSK_WIDTH')) {
            $driver->manage()->window()->setSize(
                new WebDriverDimension(
                    env('DUSK_WIDTH', 1920),
                    env('DUSK_HEIGHT', 1080)
                )
            );
        }

        return $driver;
    }

    /**
     * Run the given callback with searchable functionality enabled.
     *
     * @param  callable  $callback
     * @return void
     */
    protected function whileSearchable(callable $callback)
    {
        touch(base_path('.searchable'));

        try {
            $callback();
        } finally {
            @unlink(base_path('.searchable'));
        }
    }
}
