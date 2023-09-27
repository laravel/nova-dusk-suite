<?php

use Laravel\Dusk\Browser;
use Orchestra\Testbench\Dusk\Options;

require __DIR__.'/../vendor/autoload.php';

if (isset($_ENV['APP_SERVED']) || isset($_SERVER['APP_SERVED'])) {
    Options::$providesApplicationServer = false;
}

if (isset($_SERVER['CI']) || isset($_ENV['CI'])) {
    Options::withoutUI();

    Browser::$waitSeconds = isset($_SERVER['GITHUB_ACTIONS']) || isset($_ENV['GITHUB_ACTIONS']) ? 70 : 35;
} else {
    Options::withUI();

    Browser::$waitSeconds = 25;
}

Options::$w3cCompliant = false;

Options::addArgument('--incognito');
Options::addArgument('--disable-popup-blocking');
Options::addArgument('--force-prefers-reduced-motion');
