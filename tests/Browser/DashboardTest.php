<?php

namespace Laravel\Nova\Tests\Browser;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Keyboard;
use Laravel\Nova\Testing\Browser\Components\BreadcrumbComponent;
use Laravel\Nova\Testing\Browser\Pages\Dashboard;
use Laravel\Nova\Testing\Browser\Pages\Page;
use Laravel\Nova\Tests\DuskTestCase;

class DashboardTest extends DuskTestCase
{
    public function test_show_default_dashboard()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Dashboard())
                ->within(new BreadcrumbComponent(), function ($browser) {
                    $browser->assertSee('Dashboard')
                        ->assertCurrentPageTitle('Main');
                })
                ->waitForText('Get Started')
                ->assertSee('Welcome to Nova! Get familiar with Nova and explore its features in the documentation');

            $browser->blank();
        });
    }

    public function test_invalid_dashboard_shows_404()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Page('/dashboards/foobar'))
                ->assertNotFound();

            $browser->blank();
        });
    }

    public function test_it_can_focus_global_search_using_shortcut()
    {
        $this->requiresKeyboardSupport();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Dashboard())
                ->withKeyboard(function (Keyboard $keyboard) {
                    $keyboard->type(['/']);
                })
                ->assertFocused('@global-search');

            $browser->blank();
        });
    }
}
