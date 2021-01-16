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

    /**
     * @test
     * @dataProvider novaApiOrVendorRoutes
     */
    public function it_redirect_to_default_dashboard_after_login_from_api_or_vendor_route($given)
    {
        $this->browse(function (Browser $browser) use ($given) {
            $browser->logout()
                    ->visit($given)
                    ->type('email', 'nova@laravel.com')
                    ->type('password', 'password')
                    ->click('button[type="submit"]')
                    ->assertPathIs('/nova/dashboards/main');

            $browser->blank();
        });
    }

    public function novaApiOrVendorRoutes()
    {
        yield ['/nova-api/scripts/sidebar-tool'];
        yield ['/nova-api/scripts/custom-field'];
    }
}
