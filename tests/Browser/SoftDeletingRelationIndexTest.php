<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\DockFactory;
use Database\Factories\ShipFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class SoftDeletingRelationIndexTest extends DuskTestCase
{
    public function test_relationships_can_be_searched()
    {
        ShipFactory::new()->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new Detail('docks', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->waitForTable()
                                ->assertSeeResource(1)
                                ->searchFor('No Matching Ships')
                                ->waitForEmptyDialog()
                                ->assertDontSeeResource(1);
                    });

            $browser->blank();
        });
    }

    public function test_soft_deleting_resources_can_be_manipulated_from_their_child_index()
    {
        ShipFactory::new()->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new Detail('docks', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->withTrashed();

                        $browser->waitForTable()
                                ->assertSeeResource(1)
                                ->deleteResourceById(1)
                                ->waitForTable()
                                ->restoreResourceById(1)
                                ->waitForTable()
                                ->assertSeeResource(1);
                    });

            $browser->blank();
        });
    }

    public function test_can_navigate_to_create_relationship_screen()
    {
        DockFactory::new()->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new Detail('docks', 1))
                    ->runCreateRelation('ships')
                    ->assertQueryStringHas('viaResource', 'docks')
                    ->assertQueryStringHas('viaResourceId', '1')
                    ->assertQueryStringHas('viaRelationship', 'ships');

            $browser->blank();
        });
    }

    public function test_relations_can_be_paginated()
    {
        ShipFactory::new()->times(10)->create(['dock_id' => DockFactory::new()->create()]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new Detail('docks', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->waitForTable()
                                ->assertSeeResource(10)
                                ->assertDontSeeResource(1)
                                ->nextPage()
                                ->assertDontSeeResource(10)
                                ->assertSeeResource(1)
                                ->previousPage()
                                ->assertSeeResource(10)
                                ->assertDontSeeResource(1);
                    });

            $browser->blank();
        });
    }

    public function test_relations_can_be_sorted()
    {
        ShipFactory::new()->times(10)->create(['dock_id' => DockFactory::new()->create()]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new Detail('docks', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->waitForTable()
                                ->assertSeeResource(10)
                                ->assertSeeResource(6)
                                ->assertDontSeeResource(1)
                                ->sortBy('id')
                                ->assertDontSeeResource(10)
                                ->assertDontSeeResource(6)
                                ->assertSeeResource(5)
                                ->assertSeeResource(1);
                    });

            $browser->blank();
        });
    }

    public function test_actions_on_all_matching_relations_should_be_scoped_to_the_relation()
    {
        $ship = ShipFactory::new()->create();
        $ship2 = ShipFactory::new()->create();

        $this->browse(function (Browser $browser) use ($ship, $ship2) {
            $browser->loginAs(1)
                    ->visit(new Detail('docks', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->waitForTable()
                                ->selectAllMatching()
                                ->runAction('mark-as-active');
                    })->waitForText('The action was executed successfully.');

            $this->assertEquals(1, $ship->fresh()->active);
            $this->assertEquals(0, $ship2->fresh()->active);

            $browser->blank();
        });
    }

    public function test_deleting_all_matching_relations_is_scoped_to_the_relationships()
    {
        $ship = ShipFactory::new()->create();
        $ship2 = ShipFactory::new()->create();

        $this->browse(function (Browser $browser) use ($ship, $ship2) {
            $browser->loginAs(1)
                    ->visit(new Detail('docks', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->waitForTable()
                                ->selectAllMatching()
                                ->deleteSelected();
                    });

            $this->assertNotNull($ship->fresh()->deleted_at);
            $this->assertNull($ship2->fresh()->deleted_at);

            $browser->blank();
        });
    }
}
