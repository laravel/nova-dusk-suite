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

Artisan::command('searchable:off', function () {
    File::delete(base_path('.searchable'));
    $this->comment('Disable searchable');
});
