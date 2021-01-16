<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class ToolAuthorizationTest extends DuskTestCase
{
    /**
     * @test
     */
    public function test_tool_can_be_seen_if_authorized_to_view_it()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit('/nova')
                    ->pause(250)
                    ->assertSee('Sidebar Tool');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function test_tool_can_call_its_own_backend_routes()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit('/nova/sidebar-tool')
                    ->pause(250)
                    ->assertSee('Hello World');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function test_tool_cant_be_seen_if_not_authorized_to_view_it()
    {
        $user = User::find(1);
        $user->shouldBlockFrom('sidebarTool');

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit('/nova')
                    ->pause(250)
                    ->assertDontSee('Sidebar Tool');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function test_tool_cant_be_navigated_to_if_not_authorized_to_view_it()
    {
        $user = User::find(1);
        $user->shouldBlockFrom('sidebarTool');

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit('/nova/sidebar-tool')
                    ->pause(250)
                    ->assertSee('404')
                    ->assertDontSee('Sidebar Tool');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function test_resource_tool_can_be_seen_if_authorized_to_view_it()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->assertSee('Resource Tool');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function test_resource_tool_cant_be_seen_if_not_authorized_to_view_it()
    {
        $user = User::find(1);
        $user->shouldBlockFrom('resourceTool');

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->assertDontSee('Resource Tool');

            $browser->blank();
        });
    }
}
