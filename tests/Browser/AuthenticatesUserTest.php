<?php

namespace Laravel\Nova\Tests\Browser;

use Laravel\Dusk\Browser;
use Laravel\Nova\Nova;
use Laravel\Nova\Testing\Browser\Components\SidebarComponent;
use Laravel\Nova\Testing\Browser\Pages\Dashboard;
use Laravel\Nova\Testing\Browser\Pages\Login;
use Laravel\Nova\Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

#[Group('auth')]
class AuthenticatesUserTest extends DuskTestCase
{
    #[DataProvider('intendedUrlDataProvider')]
    public function test_it_redirect_to_intended_url_after_login($targetUrl, $expectedUrl)
    {
        $this->browse(function (Browser $browser) use ($targetUrl, $expectedUrl) {
            $browser->logout()
                ->assertGuest()
                ->visit(Nova::url($targetUrl))
                ->on(new Login)
                ->type('email', 'nova@laravel.com')
                ->type('password', 'password')
                ->clickAndWaitForReload('button[type="submit"]')
                ->assertPathIs(Nova::url($expectedUrl))
                ->assertAuthenticated();

            $browser->blank();
        });
    }

    public function test_redirect_to_login_after_logout()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Dashboard)
                ->press('Taylor Otwell')
                ->press('Logout')
                ->waitForDialog()
                ->assertDialogOpened('Are you sure you want to log out?')
                ->acceptDialog()
                ->on(new Login)
                ->assertGuest();

            $browser->blank();
        });
    }

    public function test_clear_user_association_after_logout()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Dashboard)
                ->logout()
                ->visit((new Dashboard)->url())
                ->waitForLocation('/nova/login')
                ->assertGuest();

            $browser->blank();
        });
    }

    public function test_clear_user_association_after_session_timeout()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)->visit(new Dashboard);

            $browser->deleteCookie('nova_dusk_suite_session');

            $browser->within(new SidebarComponent, function ($browser) {
                $browser->clickLink('Users');
            })->waitForLocation('/nova/login')
                ->assertGuest();

            $browser->blank();
        });
    }

    public function test_can_relogin_after_session_timeout()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)->visit(new Dashboard);

            $browser->deleteCookie('nova_dusk_suite_session')
                ->script('Nova.$emit("token-expired")');

            $browser->waitForLocation('/nova/login')
                ->type('email', 'nova@laravel.com')
                ->type('password', 'password')
                ->clickAndWaitForReload('button[type="submit"]')
                ->on(new Dashboard);

            $browser->blank();
        });
    }

    public function test_redirect_outside_of_nova_after_login()
    {
        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->assertGuest()
                ->visit('/dashboard')
                ->waitForLocation('/login')
                ->visit(new Login)
                ->type('email', 'nova@laravel.com')
                ->type('password', 'password')
                ->clickAndWaitForReload('button[type="submit"]')
                ->assertPathIs('/dashboard');

            $browser->pause(2000)->blank();
        });
    }

    public static function intendedUrlDataProvider()
    {
        yield ['/resources/users/3', '/resources/users/3'];
        yield ['/dashboards/posts-dashboard', '/dashboards/posts-dashboard'];
        yield ['/resources/users/lens/passthrough-lens', '/resources/users/lens/passthrough-lens'];
        yield ['/', '/dashboards/main'];
    }
}
