<?php

use Laravel\Dusk\Browser;
use Orchestra\Testbench\Dusk\Options;

require __DIR__.'/../vendor/autoload.php';

$CI = isset($_SERVER['CI']) || isset($_ENV['CI']);
$GITHUB_ACTIONS = isset($_SERVER['GITHUB_ACTIONS']) || isset($_ENV['GITHUB_ACTIONS']);

if ($CI) {
    Options::withoutUI();

    Browser::$waitSeconds = 35;
} else {
    Options::withUI();

    Browser::$waitSeconds = 25;
}

if ($GITHUB_ACTIONS) {
    Browser::$waitSeconds = 65;
}

Options::$w3cCompliant = false;

Options::addArgument('--incognito');
Options::addArgument('--disable-popup-blocking');
Options::addArgument('--force-prefers-reduced-motion');
