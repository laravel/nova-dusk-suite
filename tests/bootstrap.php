<?php

use Laravel\Dusk\Browser;
use Orchestra\Testbench\Dusk\Options;

require __DIR__.'/../vendor/autoload.php';

$CI = isset($_SERVER['CI']) || isset($_ENV['CI']);
$CHIPPERCI = isset($_SERVER['CHIPPER']) || isset($_ENV['CHIPPER']);
$GITHUB_ACTIONS = isset($_SERVER['GITHUB_ACTIONS']) || isset($_ENV['GITHUB_ACTIONS']);

if ($CI) {
    Options::withoutUI();

    Browser::$waitSeconds = 60;
} else {
    Options::withUI();

    Browser::$waitSeconds = 25;
}

if ($GITHUB_ACTIONS) {
    Options::noSandbox();
}

Options::$w3cCompliant = $CHIPPERCI || $GITHUB_ACTIONS ? true : false;

Options::addArgument('--incognito');
Options::addArgument('--disable-popup-blocking');
Options::addArgument('--force-prefers-reduced-motion');
