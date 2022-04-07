<?php

use Orchestra\Testbench\Dusk\Options;

require __DIR__.'/../vendor/autoload.php';

if (isset($_ENV['USE_OCTANE']) || isset($_SERVER['USE_OCTANE'])) {
    Options::$providesApplicationServer = false;
}

Options::addArgument('--disable-popup-blocking');
Options::addArgument('--force-prefers-reduced-motion');
