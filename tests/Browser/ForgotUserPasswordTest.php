<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\UserFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Nova;
use Laravel\Nova\Tests\DuskTestCase;
use Orchestra\Testbench\Attributes\WithConfig;
use PHPUnit\Framework\Attributes\Group;

#[Group('auth')]
#[WithConfig('mail.default', 'log')]
class ForgotUserPasswordTest extends DuskTestCase
{
    public function test_it_redirect_to_login_after_password_reset()
    {
        $this->browse(function (Browser $browser) {
            $user = UserFactory::new()->create();

            $browser->visit('/')
                ->logout()
                ->assertGuest()
                ->visit(Nova::url('password/reset'))
                ->waitForText('Forgot your password?')
                ->type('input[id="email"]', $user->email)
                ->click('button[type="submit"]')
                ->waitForText(__('passwords.sent'))
                ->pause(5000)
                ->waitForLocation(Nova::url('login'));

            $browser->blank();
        });
    }
}
