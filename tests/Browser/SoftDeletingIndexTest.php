<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Dock;
use App\Models\Ship;
use Database\Factories\DockFactory;
use Database\Factories\ShipFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\Index;
use Laravel\Nova\Tests\DuskTestCase;

class SoftDeletingIndexTest extends DuskTestCase
{
    public function test_can_soft_delete_a_resource_via_resource_table_row_delete_icon()
    {
        $this->browse(function (Browser $browser) {
            $dock = DockFactory::new()->create();

            $browser->loginAs(1)
                ->visit(new Index('docks'))
                ->within(new IndexComponent('docks'), function ($browser) use ($dock) {
                    $browser->waitForTable()
                        ->deleteResourceById($dock->id)
                        ->waitForEmptyDialog()
                        ->assertSee('No Dock matched the given criteria.')
                        ->assertDontSeeResource($dock->id);
                });

            $this->assertEquals(1, Dock::withTrashed()->count());

            $browser->blank();
        });
    }

    public function test_can_soft_delete_resources_using_checkboxes()
    {
        $this->browse(function (Browser $browser) {
            [$dock, $dock1, $dock2] = DockFactory::new()->times(3)->create();

            $browser->loginAs(1)
                ->visit(new Index('docks'))
                ->within(new IndexComponent('docks'), function ($browser) use ($dock, $dock1, $dock2) {
                    $browser->waitForTable()
                        ->clickCheckboxForId($dock2->id)
                        ->clickCheckboxForId($dock1->id)
                        ->deleteSelected()
                        ->waitForTable()
                        ->assertSeeResource($dock->id)
                        ->assertDontSeeResource($dock1->id)
                        ->assertDontSeeResource($dock2->id);
                });

            $browser->blank();
        });
    }

    public function test_can_restore_resources_using_checkboxes()
    {
        $this->browse(function (Browser $browser) {
            $dock = DockFactory::new()->create();
            [$dock1, $dock2] = DockFactory::new()->times(2)->create(['deleted_at' => now()]);

            $browser->loginAs(1)
                ->visit(new Index('docks'))
                ->within(new IndexComponent('docks'), function ($browser) use ($dock, $dock1, $dock2) {
                    $browser->withTrashed();

                    $browser->waitForTable()
                        ->clickCheckboxForId($dock2->id)
                        ->clickCheckboxForId($dock1->id)
                        ->restoreSelected()
                        ->waitForTable()
                        ->withoutTrashed()
                        ->waitForTable()
                        ->assertSeeResource($dock->id)
                        ->assertSeeResource($dock1->id)
                        ->assertSeeResource($dock2->id);
                });

            $browser->blank();
        });
    }

    public function test_can_force_delete_resources_using_checkboxes()
    {
        $this->browse(function (Browser $browser) {
            [$dock, $dock1, $dock2] = DockFactory::new()->times(3)->create();

            $browser->loginAs(1)
                ->visit(new Index('docks'))
                ->within(new IndexComponent('docks'), function ($browser) use ($dock, $dock1, $dock2) {
                    $browser->withTrashed();

                    $browser->waitForTable()
                        ->clickCheckboxForId($dock2->id)
                        ->clickCheckboxForId($dock1->id)
                        ->forceDeleteSelected()
                        ->waitForTable()
                        ->assertSeeResource($dock->id)
                        ->assertDontSeeResource($dock1->id)
                        ->assertDontSeeResource($dock2->id);
                });

            $browser->blank();
        });
    }

    public function test_can_soft_delete_all_matching_resources()
    {
        $this->browse(function (Browser $browser) {
            [$ship, $ship1, $ship2] = ShipFactory::new()->times(3)->create(['dock_id' => DockFactory::new()->create()]);

            $separateShip = ShipFactory::new()->create();

            $browser->loginAs(1)
                ->visit(new Detail('docks', 1))
                ->within(new IndexComponent('ships'), function ($browser) use ($ship, $ship1, $ship2) {
                    $browser->waitForTable()
                        ->selectAllMatching()
                        ->deleteSelected()
                        ->waitForEmptyDialog()
                        ->assertDontSeeResource($ship->id)
                        ->assertDontSeeResource($ship1->id)
                        ->assertDontSeeResource($ship2->id)
                        ->withTrashed()
                        ->waitForTable()
                        ->assertSeeResource($ship->id)
                        ->assertSeeResource($ship1->id)
                        ->assertSeeResource($ship2->id);
                });

            $this->assertNull($separateShip->fresh()->deleted_at);

            $browser->blank();
        });
    }

    public function test_can_restore_all_matching_resources()
    {
        ShipFactory::new()->times(3)->create([
            'dock_id' => DockFactory::new()->create(),
            'deleted_at' => now(),
        ]);

        ShipFactory::new()->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('docks', 1))
                ->within(new IndexComponent('ships'), function ($browser) {
                    $browser->withTrashed();

                    $browser->waitForTable()
                        ->selectAllMatching()
                        ->restoreSelected()
                        ->waitForTable()
                        ->assertSeeResource(1)
                        ->assertSeeResource(2)
                        ->assertSeeResource(3);
                });

            $this->assertEquals(4, Ship::count());
            $this->assertEquals(0, Ship::onlyTrashed()->count());

            $browser->blank();
        });
    }

    public function test_can_force_delete_all_matching_resources()
    {
        $this->browse(function (Browser $browser) {
            [$ship, $ship1, $ship2] = ShipFactory::new()->times(3)->create([
                'dock_id' => DockFactory::new()->create(),
                'deleted_at' => now(),
            ]);

            $separateShip = ShipFactory::new()->create();

            $browser->loginAs(1)
                ->visit(new Detail('docks', 1))
                ->within(new IndexComponent('ships'), function ($browser) use ($ship, $ship1, $ship2) {
                    $browser->withTrashed();

                    $browser->waitForTable()
                        ->selectAllMatching()
                        ->forceDeleteSelected()
                        ->waitForEmptyDialog()
                        ->assertDontSeeResource($ship->id)
                        ->assertDontSeeResource($ship1->id)
                        ->assertDontSeeResource($ship2->id);
                });

            $this->assertNotNull($separateShip->fresh());
            $this->assertEquals(1, Ship::count());
            $this->assertEquals(0, Ship::onlyTrashed()->count());

            $browser->blank();
        });
    }

    public function test_soft_deleted_resource_is_still_viewable_with_proper_trash_state()
    {
        $this->browse(function (Browser $browser) {
            $dock = DockFactory::new()->create();

            $browser->loginAs(1)
                ->visit(new Index('docks'))
                ->within(new IndexComponent('docks'), function ($browser) use ($dock) {
                    $browser->withTrashed()
                        ->waitForTable()
                        ->deleteResourceById($dock->id)
                        ->waitForTable()
                        ->assertSeeResource($dock->id);
                });

            $this->assertEquals(1, Dock::withTrashed()->count());

            $browser->blank();
        });
    }

    public function test_only_soft_deleted_resources_may_be_listed()
    {
        $this->browse(function (Browser $browser) {
            $ship = DockFactory::new()->create();
            $ship1 = DockFactory::new()->create(['deleted_at' => now()]);

            $browser->loginAs(1)
                ->visit(new Index('docks'))
                ->within(new IndexComponent('docks'), function ($browser) use ($ship, $ship1) {
                    $browser->waitForTable()
                        ->assertSeeResource($ship->id)
                        ->assertDontSeeResource($ship1->id);

                    $browser->onlyTrashed()
                        ->waitForTable()
                        ->assertDontSeeResource($ship->id)
                        ->assertSeeResource($ship1->id);
                });

            $browser->blank();
        });
    }

    public function test_soft_deleted_resources_may_be_restored_via_row_icon()
    {
        $this->browse(function (Browser $browser) {
            $dock = DockFactory::new()->create();

            $browser->loginAs(1)
                ->visit(new Index('docks'))
                ->within(new IndexComponent('docks'), function ($browser) use ($dock) {
                    $browser->withTrashed()
                        ->waitForTable()
                        ->deleteResourceById($dock->id)
                        ->waitForTable()
                        ->restoreResourceById($dock->id)
                        ->waitForTable()
                        ->assertSeeResource($dock->id);
                });

            $this->assertEquals(1, Dock::count());

            $browser->blank();
        });
    }
}
