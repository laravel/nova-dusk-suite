<?php

namespace Tests\Browser;

use App\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class DetailTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function can_view_resource_attributes()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->assertSee('Taylor Otwell')
                    ->assertSee('taylor@laravel.com');
        });
    }

    /**
     * @test
     */
    public function can_run_actions_on_resource()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->runAction('mark-as-active');

            $this->assertEquals(1, User::find(1)->active);
        });
    }

    /**
     * @test
     */
    public function actions_can_be_cancelled_without_effect()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->cancelAction('mark-as-active');

            $this->assertEquals(0, User::find(1)->active);
        });
    }

    /**
     * @test
     */
    public function user_can_be_deleted()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 3))
                    ->delete();

            $this->assertNull(User::where('id', 3)->first());
        });
    }
}
