<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Dashboard;
use Laravel\Nova\Tests\DuskTestCase;

class AuthenticatesUserTest extends DuskTestCase
{
    /**
     * @test
     * @dataProvider intendedUrlDataProvider
     */
    public function it_redirect_to_intended_url_after_login($targetUrl, $expectedUrl)
    {
        $this->browse(function (Browser $browser) use ($targetUrl, $expectedUrl) {
            $browser->logout()
                    ->visit($targetUrl)
                    ->waitForLocation('/nova/login')
                    ->assertPathIs('/nova/login')
                    ->type('email', 'nova@laravel.com')
                    ->type('password', 'password')
                    ->click('button[type="submit"]')
                    ->waitForLocation($expectedUrl)
                    ->assertPathIs($expectedUrl);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function it_redirect_to_login_after_logout()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Dashboard())
                    ->press('Taylor Otwell')
                    ->press('Logout')
                    ->waitForLocation('/nova/login')
                    ->assertPathIs('/nova/login')
                    ->assertGuest();

            $browser->blank();
        });
    }

    public function intendedUrlDataProvider()
    {
        yield ['/nova/resources/users/3', '/nova/resources/users/3'];
        yield ['/nova/dashboards/posts-dashboard', '/nova/dashboards/posts-dashboard'];
        yield ['/nova/resources/users/lens/passthrough-lens', '/nova/resources/users/lens/passthrough-lens'];
        yield ['/nova/', '/nova/dashboards/main'];
    }
}
