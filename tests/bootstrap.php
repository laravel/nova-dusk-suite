<?php

require __DIR__.'/../vendor/autoload.php';

if (isset($_ENV['USE_OCTANE']) || isset($_SERVER['USE_OCTANE'])) {
    Orchestra\Testbench\Dusk\Options::$providesApplicationServer = false;
}
