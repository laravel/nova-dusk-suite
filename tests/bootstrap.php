<?php

use Orchestra\Testbench\Dusk\Options;

require __DIR__.'/../vendor/autoload.php';

if (isset($_SERVER['CI']) || isset($_ENV['CI'])) {
    Options::withoutUI();
} else {
    Options::withUI();
}

Options::$w3cCompliant = false;

Options::addArgument('--disable-popup-blocking');
Options::addArgument('--force-prefers-reduced-motion');
