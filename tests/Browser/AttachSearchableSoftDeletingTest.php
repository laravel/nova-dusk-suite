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

class AttachSearchableSoftDeletingTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function resource_can_be_attached()
    {
        $this->seed();

        $captain = factory(Captain::class)->create();
        $ship = factory(Ship::class)->create();

        $this->browse(function (Browser $browser) use ($captain, $ship) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Attach('captains', $captain->id, 'ships'))
                    ->searchRelation('ships', 1)
                    ->selectCurrentRelation('ships')
                    ->clickAttach();

            $this->assertCount(1, $captain->fresh()->ships);
        });
    }

    /**
     * @test
     */
    public function fields_on_intermediate_table_should_be_stored()
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
                    ->type('@notes', 'Test Notes')
                    ->clickAttach();

            $this->assertEquals($role->id, User::find(1)->roles->first()->id);
            $this->assertEquals('Test Notes', User::find(1)->roles->first()->pivot->notes);
        });
    }

    /**
     * @test
     */
    public function validation_errors_are_displayed()
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
                    ->clickAttach()
                    ->assertSee('The role field is required.');

            $this->assertNull(User::find(1)->roles->first());
        });
    }
}
