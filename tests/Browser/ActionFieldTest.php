<?php

namespace Tests\Browser;

use App\Role;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\DetailComponent;
use Tests\Browser\Components\IndexComponent;
use Tests\DuskTestCase;

class ActionFieldTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function actions_can_be_instantly_dispatched()
    {
        $this->seed();

        $user = User::find(1);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->within(new DetailComponent('users', 1), function ($browser) {
                        $browser->runInstantAction('redirect-to-google')
                                ->assertMissing('Nova')
                                ->assertHostIs('www.google.com');
                    });
        });
    }

    /**
     * @test
     */
    public function actions_can_receive_and_utilize_field_input()
    {
        $this->seed();

        $user = User::find(1);
        $role = factory(Role::class)->create();
        $user->roles()->attach($role);

        $this->browse(function (Browser $browser) use ($user, $role) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->within(new IndexComponent('roles'), function ($browser) {
                        $browser->clickCheckboxForId(1)
                                ->runAction('update-pivot-notes', function ($browser) {
                                    $browser->type('@notes', 'Custom Notes');
                                });
                    });

            $this->assertEquals('Custom Notes', $user->fresh()->roles->first()->pivot->notes);
        });
    }
}
