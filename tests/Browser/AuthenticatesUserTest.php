<?php

namespace Laravel\Nova\Tests\Browser;

use Laravel\Dusk\Browser;
use Laravel\Nova\Tests\DuskTestCase;

class AuthenticatesUserTest extends DuskTestCase
{
    /**
     * @test
     */
    public function it_redirect_to_intended_url_after_login()
    {
        $this->setupLaravel();

        $this->browse(function (Browser $browser) {
            $browser->visit('/nova/resources/users/3')
                    ->assertPathIs('/nova/login')
                    ->type('email', 'nova@laravel.com')
                    ->type('password', 'password')
                    ->click('button[type="submit"]')
                    ->assertPathIs('/nova/resources/users/3');

            $browser->blank();
        });
    }
}
