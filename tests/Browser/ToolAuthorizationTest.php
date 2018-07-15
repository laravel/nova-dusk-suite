<?php

namespace Tests\Browser;

use App\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ToolAuthorizationTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function test_tool_can_be_seen_if_authorized_to_view_it()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit('/nova')
                    ->pause(250)
                    ->assertSee('Sidebar Tool');
        });
    }

    /**
     * @test
     */
    public function test_tool_cant_be_seen_if_not_authorized_to_view_it()
    {
        $this->seed();

        $user = User::find(1);
        $user->shouldBlockFrom('sidebarTool');

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit('/nova')
                    ->pause(250)
                    ->assertDontSee('Sidebar Tool');
        });
    }

    /**
     * @test
     */
    public function test_tool_cant_be_navigated_to_if_not_authorized_to_view_it()
    {
        $this->seed();

        $user = User::find(1);
        $user->shouldBlockFrom('sidebarTool');

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit('/nova/sidebar-tool')
                    ->pause(250)
                    ->assertSee('404')
                    ->assertDontSee('Sidebar Tool');
        });
    }

    /**
     * @test
     */
    public function test_resource_tool_can_be_seen_if_authorized_to_view_it()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->assertSee('Resource Tool');
        });
    }

    /**
     * @test
     */
    public function test_resource_tool_cant_be_seen_if_not_authorized_to_view_it()
    {
        $this->seed();

        $user = User::find(1);
        $user->shouldBlockFrom('resourceTool');

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->assertDontSee('Resource Tool');
        });
    }
}
