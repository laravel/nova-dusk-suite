<?php

namespace Tests\Browser;

use App\Role;
use App\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\IndexComponent;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PivotActionTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function actions_can_be_executed_against_pivot_rows()
    {
        $this->seed();

        $role = factory(Role::class)->create();

        $this->browse(function (Browser $browser) use ($role) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->within(new IndexComponent('roles'), function ($browser) {
                        $browser->click('@attach-button');
                    })
                    ->on(new Pages\Attach('users', 1, 'roles'))
                    ->selectAttachable($role->id)
                    ->clickAttach();

            $this->assertEquals($role->id, User::find(1)->roles->first()->id);
        });
    }
}
