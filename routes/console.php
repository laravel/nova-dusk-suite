<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('searchable:on', function () {
    File::put(base_path('.searchable'), '');
    $this->comment('Enable searchable');
});

Artisan::command('inline-create:off', function () {
    File::delete(base_path('.inline-create'));
    $this->comment('Disable inline-create');
});

Artisan::command('inline-create:on', function () {
    File::put(base_path('.inline-create'), '');
    $this->comment('Enable inline-create');
});

Artisan::command('inline-create:off', function () {
    File::delete(base_path('.inline-create'));
    $this->comment('Disable inline-create');
});
