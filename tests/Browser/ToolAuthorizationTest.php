<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Dashboard;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\Page;
use Laravel\Nova\Tests\DuskTestCase;

class ToolAuthorizationTest extends DuskTestCase
{
    public function test_tool_can_be_seen_if_authorized_to_view_it()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Dashboard())
                ->pause(250)
                ->assertSee('Sidebar Tool');

            $browser->blank();
        });
    }

    public function test_tool_can_call_its_own_backend_routes()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Page('/sidebar-tool'))
                ->assertOk()
                ->assertWithoutBreadcrumb()
                ->waitForTextIn('@nova-content', "We're in a black hole.")
                ->pause(1500)
                ->assertSee('Hello World');

            $browser->blank();
        });
    }

    public function test_tool_cant_be_seen_if_not_authorized_to_view_it()
    {
        User::find(1)->shouldBlockFrom('sidebarTool');

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Dashboard())
                ->pause(250)
                ->assertDontSee('Sidebar Tool');

            $browser->blank();
        });
    }

    public function test_tool_cant_be_navigated_to_if_not_authorized_to_view_it()
    {
        User::find(1)->shouldBlockFrom('sidebarTool');

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Page('/sidebar-tool'))
                ->waitForText('403')
                ->assertSee("The government won't let us show you what's behind these doors…")
                ->assertDontSee('Sidebar Tool');

            $browser->blank();
        });
    }

    public function test_resource_tool_can_be_seen_if_authorized_to_view_it()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->waitForText('Resource Tool')
                ->assertSee('Resource Tool for Taylor Otwell');

            $browser->blank();
        });
    }

    public function test_resource_tool_cant_be_seen_if_not_authorized_to_view_it()
    {
        User::find(1)->shouldBlockFrom('resourceTool');

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->pause(250)
                ->assertDontSee('Resource Tool');

            $browser->blank();
        });
    }
}
