<?php

namespace Laravel\Nova\Tests\Browser;

use Laravel\Dusk\Browser;
use Laravel\Nova\Nova;
use Laravel\Nova\Testing\Browser\Pages\Dashboard;
use Laravel\Nova\Tests\DuskTestCase;

class CustomAuthenticatesUserTest extends DuskTestCase
{
    /**
     * @test
     * @dataProvider intendedUrlDataProvider
     */
    public function it_redirect_to_intended_url_after_login($targetUrl, $expectedUrl)
    {
        $this->beforeServingApplication(function ($app, $config) {
            $config->set('nova.routes.login', '/login');
            $config->set('nova.routes.logout', '/logout');

            Nova::$withAuthentication = false;
        });

        $this->browse(function (Browser $browser) use ($targetUrl, $expectedUrl) {
            $browser->logout()
                    ->assertGuest()
                    ->visit(Nova::url($targetUrl))
                    ->waitForLocation('/login')
                    ->type('email', 'nova@laravel.com')
                    ->type('password', 'password')
                    ->clickAndWaitForReload('button[type="submit"]')
                    ->assertPathIs(Nova::url($expectedUrl));

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function it_redirect_to_login_after_logout()
    {
        $this->beforeServingApplication(function ($app, $config) {
            $config->set('nova.routes.login', '/login');
            $config->set('nova.routes.logout', '/logout');

            Nova::$withAuthentication = false;
        });

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new Dashboard())
                    ->press('Taylor Otwell')
                    ->press('Logout')
                    ->assertDialogOpened('Are you sure you want to log out?')
                    ->acceptDialog()
                    ->waitForLocation('/login')
                    ->assertGuest();

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function it_clear_user_association_after_logout()
    {
        $this->beforeServingApplication(function ($app, $config) {
            $config->set('nova.routes.login', '/login');
            $config->set('nova.routes.logout', '/logout');

            Nova::$withAuthentication = false;
        });

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new Dashboard())
                    ->logout()
                    ->visit((new Dashboard())->url())
                    ->waitForLocation('/login')
                    ->assertGuest();

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function it_clear_user_association_after_session_timeout()
    {
        $this->beforeServingApplication(function ($app, $config) {
            $config->set('nova.routes.login', '/login');
            $config->set('nova.routes.logout', '/logout');

            Nova::$withAuthentication = false;
        });

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)->visit(new Dashboard());

            $browser->deleteCookie('nova_dusk_suite_session');

            $browser->within('@sidebar-menu', function ($browser) {
                $browser->clickLink('Books');
            })->waitForLocation('/login')
            ->assertGuest();

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function it_can_relogin_after_session_timeout()
    {
        $this->beforeServingApplication(function ($app, $config) {
            $config->set('nova.routes.login', '/login');
            $config->set('nova.routes.logout', '/logout');

            Nova::$withAuthentication = false;
        });

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)->visit(new Dashboard());

            $browser->deleteCookie('nova_dusk_suite_session')
                    ->script('Nova.$emit("token-expired")');

            $browser->waitForLocation('/login')
                ->type('email', 'nova@laravel.com')
                ->type('password', 'password')
                ->clickAndWaitForReload('button[type="submit"]')
                ->on(new Dashboard);

            $browser->blank();
        });
    }

    public function intendedUrlDataProvider()
    {
        yield ['/resources/users/3', '/resources/users/3'];
        yield ['/dashboards/posts-dashboard', '/dashboards/posts-dashboard'];
        yield ['/resources/users/lens/passthrough-lens', '/resources/users/lens/passthrough-lens'];
        yield ['/', '/dashboards/main'];
    }
}
