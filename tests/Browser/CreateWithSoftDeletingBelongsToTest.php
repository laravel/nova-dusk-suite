<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\DockFactory;
use Database\Factories\ShipFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class CreateWithSoftDeletingBelongsToTest extends DuskTestCase
{
    /**
     * @test
     */
    public function test_parent_select_is_locked_when_creating_child_of_soft_deleted_resource()
    {
        $dock = DockFactory::new()->create(['deleted_at' => now()]);

        $this->browse(function (Browser $browser) use ($dock) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', $dock->id))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->waitFor('@create-button')->click('@create-button');
                    })
                    ->on(new Create('ships'))
                    ->assertDisabled('@dock')
                    ->type('@name', 'Test Ship')
                    ->create();

            $this->assertCount(1, $dock->fresh()->ships);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function non_searchable_belongs_to_respects_with_trashed_checkbox_state()
    {
        $ship = ShipFactory::new()->create(['deleted_at' => now()]);
        $ship2 = ShipFactory::new()->create();

        $this->browse(function (Browser $browser) use ($ship, $ship2) {
            $browser->loginAs(User::find(1))
                    ->visit(new Create('sails'))
                    ->assertSelectMissingOption('@ship', $ship->id)
                    ->assertSelectHasOption('@ship', $ship2->id)
                    ->withTrashedRelation('ships')
                    ->assertSelectHasOption('@ship', $ship->id)
                    ->assertSelectHasOption('@ship', $ship2->id)
                    ->select('@ship', $ship->id)
                    ->type('@inches', 25)
                    ->create();

            $this->assertCount(1, $ship->fresh()->sails);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function unable_to_uncheck_with_trashed_if_currently_selected_non_searchable_parent_is_trashed()
    {
        $ship = ShipFactory::new()->create(['deleted_at' => now()]);

        $this->browse(function (Browser $browser) use ($ship) {
            $browser->loginAs(User::find(1))
                    ->visit(new Create('sails'))
                    ->withTrashedRelation('ships')
                    ->select('@ship', $ship->id)
                    ->withoutTrashedRelation('ships')
                    // Ideally would use assertChecked here but RemoteWebDriver
                    // returns unchecked when it clearly is checked?
                    ->type('@inches', 25)
                    ->create();

            $this->assertCount(1, $ship->fresh()->sails);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function searchable_belongs_to_respects_with_trashed_checkbox_state()
    {
        $this->whileSearchable(function () {
            $dock = DockFactory::new()->create(['deleted_at' => now()]);

            $this->browse(function (Browser $browser) use ($dock) {
                $browser->loginAs(User::find(1))
                        ->visit(new Create('ships'))
                        ->searchRelation('docks', '1')
                        ->pause(1500)
                        ->assertNoRelationSearchResults('docks')
                        ->withTrashedRelation('docks')
                        ->searchAndSelectFirstRelation('docks', '1')
                        ->type('@name', 'Test Ship')
                        ->create();

                $this->assertCount(1, $dock->fresh()->ships);

                $browser->blank();
            });
        });
    }
}
