<?php

namespace Tests\Browser;

use App\Role;
use App\Ship;
use App\User;
use App\Captain;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\IndexComponent;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UpdateAttachedSoftDeletingTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function attached_resource_can_be_updated()
    {
        $this->seed();

        $ship = factory(Ship::class)->create(['deleted_at' => now()]);
        $captain = factory(Captain::class)->create();
        $captain->ships()->attach($ship);

        $this->browse(function (Browser $browser) use ($ship, $captain) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('captains', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->withTrashed()->click('@1-edit-attached-button');
                    })
                    ->on(new Pages\UpdateAttached('captains', 1, 'ships', 1))
                    ->assertDisabled('@attachable-select')
                    ->type('@notes', 'Test Notes')
                    ->update();

            $this->assertEquals('Test Notes', $captain->fresh()->ships()->withTrashed()->get()->first()->pivot->notes);
        });
    }

    public function attached_resource_can_be_updated_and_can_continue_editing()
    {
        $this->seed();

        $user = User::find(1);
        $role = factory(Role::class)->create();
        $user->roles()->attach($role, ['notes' => 'Test Notes']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->within(new IndexComponent('roles'), function ($browser) {
                        $browser->click('@1-edit-attached-button');
                    })
                    ->on(new Pages\UpdateAttached('users', 1, 'roles', 1))
                    ->type('@notes', 'Test Notes Updated')
                    ->updateAndContinueEditing();

            $browser->assertPathIs('/nova/resources/users/1/edit-attached/roles/1');

            $this->assertEquals('Test Notes Updated', User::find(1)->roles->first()->pivot->notes);
        });
    }
}
