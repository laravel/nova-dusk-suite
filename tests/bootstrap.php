<?php

use Orchestra\Testbench\Dusk\Options;

require __DIR__.'/../vendor/autoload.php';

if (isset($_ENV['APP_SERVED']) || isset($_SERVER['APP_SERVED'])) {
    Options::$providesApplicationServer = false;
}

Options::addArgument('--disable-popup-blocking');
Options::addArgument('--force-prefers-reduced-motion');
