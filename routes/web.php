<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Nova\URL;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::get('/subscribers/dashboard', function () {
    return view('dashboard');
})->middleware(['auth:web-subscribers'])->name('subscribers.dashboard');

Route::prefix('tests')
    ->middleware(['web'])
    ->group(function ($router) {
        $router->post('verify-user/{user}', function (User $user) {
            if (is_null($user->email_verified_at)) {
                $user->email_verified_at = now();
            }

            $user->active = true;
            $user->save();

            return Inertia::location(URL::make('/resources/users/'.$user->id));
        });
    });

require __DIR__.'/auth.php';
