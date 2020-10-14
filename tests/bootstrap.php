<?php

require __DIR__.'/../vendor/autoload.php';

if (isset($_SERVER['CI']) || isset($_ENV['CI'])) {
    Orchestra\Testbench\Dusk\Options::withoutUI();
} else {
    Orchestra\Testbench\Dusk\Options::withUI();
}
