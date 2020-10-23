<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\CaptainFactory;
use Database\Factories\ShipFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\UpdateAttached;
use Laravel\Nova\Tests\DuskTestCase;

class UpdateAttachedSoftDeletingTest extends DuskTestCase
{
    /**
     * @test
     */
    public function attached_resource_can_be_updated()
    {
        $this->setupLaravel();

        $ship = ShipFactory::new()->create(['deleted_at' => now()]);
        $captain = CaptainFactory::new()->create();
        $captain->ships()->attach($ship);

        $this->browse(function (Browser $browser) use ($captain) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('captains', 1))
                    ->waitFor('@ships-index-component', 10)
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->withTrashed()->pause(175)->click('@1-edit-attached-button');
                    })
                    ->on(new UpdateAttached('captains', 1, 'ships', 1))
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

        $ship = ShipFactory::new()->create(['deleted_at' => now()]);
        $captain = CaptainFactory::new()->create();
        $captain->ships()->attach($ship);

        $this->browse(function (Browser $browser) use ($captain) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('captains', 1))
                    ->waitFor('@ships-index-component', 10)
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->withTrashed()->click('@1-edit-attached-button');
                    })
                    ->on(new UpdateAttached('captains', 1, 'ships', 1))
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
