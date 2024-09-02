<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Laravel\Nova\Nova;
use Laravel\Nova\Tests\DuskTestCase;

class ResetPasswordTest extends DuskTestCase
{
    public function test_it_can_reset_user_password_and_navigate_to_nova()
    {
        $user = User::find(1);
        $passwordBroker = $this->app->make('auth.password.broker');

        $token = $passwordBroker->createToken($user);

        $this->browse(function (Browser $browser) use ($user, $token) {
            $browser->visit("/nova/password/reset/{$token}?email={$user->email}")
                ->type('password', 'password!!')
                ->type('password_confirmation', 'password!!')
                ->clickAndWaitForRequest('button[type="submit"]')
                ->assertPathIs(Nova::url('/login'))
                ->assertGuest();

            $browser->blank();

            $user->refresh();
            $this->assertTrue(password_verify('password!!', $user->password));
        });
    }
}
