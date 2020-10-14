<?php

namespace Laravel\Nova\Tests\Browser;

use App\Captain;
use App\Ship;
use App\User;
use Laravel\Dusk\Browser;
use Laravel\Nova\Tests\Browser\Components\IndexComponent;
use Laravel\Nova\Tests\DuskTestCase;

class UpdateAttachedSoftDeletingTest extends DuskTestCase
{
    /**
     * @test
     */
    public function attached_resource_can_be_updated()
    {
        $this->setupLaravel();

        $ship = factory(Ship::class)->create(['deleted_at' => now()]);
        $captain = factory(Captain::class)->create();
        $captain->ships()->attach($ship);

        $this->browse(function (Browser $browser) use ($captain) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('captains', 1))
                    ->waitFor('@ships-index-component', 5)
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->withTrashed()->pause(175)->click('@1-edit-attached-button');
                    })
                    ->on(new Pages\UpdateAttached('captains', 1, 'ships', 1))
                    ->assertDisabled('@attachable-select')
                    ->type('@notes', 'Test Notes')
                    ->update();

            $this->assertEquals(
                'Test Notes',
                $captain->fresh()->ships()->withTrashed()->get()->first()->pivot->notes
            );

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function attached_resource_can_be_updated_and_can_continue_editing()
    {
        $this->setupLaravel();

        $ship = factory(Ship::class)->create(['deleted_at' => now()]);
        $captain = factory(Captain::class)->create();
        $captain->ships()->attach($ship);

        $this->browse(function (Browser $browser) use ($captain) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('captains', 1))
                    ->waitFor('@ships-index-component', 5)
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->withTrashed()->click('@1-edit-attached-button');
                    })
                    ->on(new Pages\UpdateAttached('captains', 1, 'ships', 1))
                    ->assertDisabled('@attachable-select')
                    ->type('@notes', 'Test Notes')
                    ->updateAndContinueEditing();

            $browser->assertPathIs('/nova/resources/captains/1/edit-attached/ships/1');

            $this->assertEquals(
                'Test Notes',
                $captain->fresh()->ships()->withTrashed()->get()->first()->pivot->notes
            );

            $browser->blank();
        });
    }
}
