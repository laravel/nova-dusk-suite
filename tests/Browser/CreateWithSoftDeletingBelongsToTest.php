<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Sail;
use Database\Factories\DockFactory;
use Database\Factories\ShipFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\Controls\RelationSelectControlComponent;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class CreateWithSoftDeletingBelongsToTest extends DuskTestCase
{
    public function test_parent_select_is_locked_when_creating_child_of_soft_deleted_resource()
    {
        $dock = DockFactory::new()->create(['deleted_at' => now()]);

        $this->browse(function (Browser $browser) use ($dock) {
            $browser->loginAs(1)
                ->visit(new Detail('docks', $dock->id))
                ->runCreateRelation('ships')
                ->assertSelectedSearchResult('docks', $dock->name)
                ->type('@name', 'Test Ship')
                ->create()
                ->waitForText('The ship was created!');

            $this->assertSame(1, $dock->loadCount('ships')->ships_count);

            $browser->blank();
        });
    }

    public function test_select_belongs_to_respects_with_trashed_checkbox_state()
    {
        $ship = ShipFactory::new()->create(['deleted_at' => now()]);
        $ship2 = ShipFactory::new()->create();

        $this->browse(function (Browser $browser) use ($ship, $ship2) {
            $browser->loginAs(1)
                ->visit(new Create('sails'))
                ->whenAvailable(new RelationSelectControlComponent('ships'), function ($browser) use ($ship, $ship2) {
                    $browser->assertSelectMissingOption('', $ship->id)
                        ->assertSelectHasOption('', $ship2->id);
                })
                ->withTrashedRelation('ships')
                ->whenAvailable(new RelationSelectControlComponent('ships'), function ($browser) use ($ship, $ship2) {
                    $browser->assertSelectHasOptions('', [$ship->id, $ship2->id])
                        ->select('', $ship->id);
                })
                ->type('@inches', 25)
                ->create()
                ->waitForText('The sail was created!');

            $this->assertSame(1, $ship->loadCount('sails')->sails_count);

            $browser->blank();
        });
    }

    public function test_uncheck_with_trashed_can_be_saved_when_parent_is_trashed()
    {
        $ship = ShipFactory::new()->create(['deleted_at' => now()]);

        $this->browse(function (Browser $browser) use ($ship) {
            $browser->loginAs(1)
                ->visit(new Create('sails'))
                ->withTrashedRelation('ships')
                ->selectRelation('ships', $ship->id)
                ->withoutTrashedRelation('ships')
                    // Ideally would use assertChecked here but RemoteWebDriver
                    // returns unchecked when it clearly is checked?
                ->type('@name', 'Sail name')
                ->type('@inches', 25)
                ->create()
                ->waitForText('The sail was created!');

            $this->assertSame(1, Sail::whereBelongsTo($ship)->count());

            $browser->blank();
        });
    }

    public function test_searchable_belongs_to_respects_with_trashed_checkbox_state()
    {
        $this->defineApplicationStates('searchable');

        $this->browse(function (Browser $browser) {
            $dock = DockFactory::new()->create(['deleted_at' => now()]);

            $browser->loginAs(1)
                ->visit(new Create('ships'))
                ->searchRelation('docks', '1')
                ->pause(1500)
                ->assertNoRelationSearchResults('docks')
                ->withTrashedRelation('docks')
                ->searchFirstRelation('docks', '1')
                ->type('@name', 'Test Ship')
                ->create()
                ->waitForText('The ship was created!');

            $this->assertSame(1, $dock->loadCount('ships')->ships_count);

            $browser->blank();
        });
    }
}
