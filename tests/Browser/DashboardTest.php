<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Dashboard;
use Laravel\Nova\Tests\DuskTestCase;

class DashboardTest extends DuskTestCase
{
    /**
     * @test
     */
    public function show_default_dashboard()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Dashboard())
                    ->assertSee('Get Started');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function invalid_dashboard_shows_404()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Dashboard('foobar'))
                    ->waitForText('404', 15)
                    ->assertPathIs('/nova/404');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function it_can_focus_global_search_using_shortcut()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Dashboard())
                    ->keys('', ['/'])
                    ->assertFocused('@global-search');

            $browser->blank();
        });
    }
}
