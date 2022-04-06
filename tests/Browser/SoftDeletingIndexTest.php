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
    /**
     * @test
     */
    public function can_soft_delete_a_resource_via_resource_table_row_delete_icon()
    {
        DockFactory::new()->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new Index('docks'))
                    ->within(new IndexComponent('docks'), function ($browser) {
                        $browser->waitForTable()
                                ->deleteResourceById(1)
                                ->waitForEmptyDialog()
                                ->assertSee('No Dock matched the given criteria.')
                                ->assertDontSeeResource(1);
                    });

            $this->assertEquals(1, Dock::withTrashed()->count());

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_soft_delete_resources_using_checkboxes()
    {
        DockFactory::new()->times(3)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new Index('docks'))
                    ->within(new IndexComponent('docks'), function ($browser) {
                        $browser->waitForTable()
                            ->clickCheckboxForId(3)
                            ->clickCheckboxForId(2)
                            ->deleteSelected()
                            ->waitForTable()
                            ->assertSeeResource(1)
                            ->assertDontSeeResource(2)
                            ->assertDontSeeResource(3);
                    });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_restore_resources_using_checkboxes()
    {
        DockFactory::new()->create();
        DockFactory::new()->times(2)->create(['deleted_at' => now()]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new Index('docks'))
                    ->within(new IndexComponent('docks'), function ($browser) {
                        $browser->withTrashed();

                        $browser->waitForTable()
                            ->clickCheckboxForId(3)
                            ->clickCheckboxForId(2)
                            ->restoreSelected()
                            ->waitForTable()
                            ->withoutTrashed()
                            ->waitForTable()
                            ->waitForText('Docks')
                            ->assertSeeResource(1)
                            ->assertSeeResource(2)
                            ->assertSeeResource(3);
                    });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_force_delete_resources_using_checkboxes()
    {
        DockFactory::new()->times(3)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new Index('docks'))
                    ->within(new IndexComponent('docks'), function ($browser) {
                        $browser->withTrashed();

                        $browser->waitForTable()
                            ->clickCheckboxForId(3)
                            ->clickCheckboxForId(2)
                            ->forceDeleteSelected()
                            ->waitForTable()
                            ->assertSeeResource(1)
                            ->assertDontSeeResource(2)
                            ->assertDontSeeResource(3);
                    });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_soft_delete_all_matching_resources()
    {
        ShipFactory::new()->times(3)->create(['dock_id' => DockFactory::new()->create()]);

        $separateShip = ShipFactory::new()->create();

        $this->browse(function (Browser $browser) use ($separateShip) {
            $browser->loginAs(1)
                    ->visit(new Detail('docks', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->waitForTable()
                            ->selectAllMatching()
                            ->deleteSelected()
                            ->waitForEmptyDialog()
                            ->assertDontSeeResource(1)
                            ->assertDontSeeResource(2)
                            ->assertDontSeeResource(3)
                            ->withTrashed()
                            ->waitForTable()
                            ->assertSeeResource(1)
                            ->assertSeeResource(2)
                            ->assertSeeResource(3);
                    });

            $this->assertNull($separateShip->fresh()->deleted_at);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_restore_all_matching_resources()
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

    /**
     * @test
     */
    public function can_force_delete_all_matching_resources()
    {
        ShipFactory::new()->times(3)->create([
            'dock_id' => DockFactory::new()->create(),
            'deleted_at' => now(),
        ]);

        $separateShip = ShipFactory::new()->create();

        $this->browse(function (Browser $browser) use ($separateShip) {
            $browser->loginAs(1)
                    ->visit(new Detail('docks', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->withTrashed();

                        $browser->waitForTable()
                            ->selectAllMatching()
                            ->forceDeleteSelected()
                            ->waitForEmptyDialog()
                            ->assertDontSeeResource(1)
                            ->assertDontSeeResource(2)
                            ->assertDontSeeResource(3);
                    });

            $this->assertNotNull($separateShip->fresh());
            $this->assertEquals(1, Ship::count());
            $this->assertEquals(0, Ship::onlyTrashed()->count());

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function soft_deleted_resource_is_still_viewable_with_proper_trash_state()
    {
        $dock = DockFactory::new()->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new Index('docks'))
                    ->within(new IndexComponent('docks'), function ($browser) {
                        $browser->withTrashed()
                                ->waitForTable()
                                ->deleteResourceById(1)
                                ->waitForTable()
                                ->assertSeeResource(1);
                    });

            $this->assertEquals(1, Dock::withTrashed()->count());

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function only_soft_deleted_resources_may_be_listed()
    {
        DockFactory::new()->create();
        DockFactory::new()->create(['deleted_at' => now()]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new Index('docks'))
                    ->within(new IndexComponent('docks'), function ($browser) {
                        $browser->waitForTable()
                                ->assertSeeResource(1)
                                ->assertDontSeeResource(2);

                        $browser->onlyTrashed()
                                ->waitForTable()
                                ->assertDontSeeResource(1)
                                ->assertSeeResource(2);
                    });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function soft_deleted_resources_may_be_restored_via_row_icon()
    {
        DockFactory::new()->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new Index('docks'))
                    ->within(new IndexComponent('docks'), function ($browser) {
                        $browser->withTrashed()
                                ->waitForTable()
                                ->deleteResourceById(1)
                                ->waitForTable()
                                ->restoreResourceById(1)
                                ->waitForTable()
                                ->assertSeeResource(1);
                    });

            $this->assertEquals(1, Dock::count());

            $browser->blank();
        });
    }
}
