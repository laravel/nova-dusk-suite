<?php

namespace Tests\Browser;

use App\Dock;
use App\Ship;
use App\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\LensComponent;
use Tests\Browser\Components\IndexComponent;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class SoftDeletingLensTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function can_soft_delete_a_resource_via_resource_table_row_delete_icon()
    {
        $this->seed();

        $dock = factory(Dock::class)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Lens('docks', 'passthrough-with-trashed-lens'))
                    ->within(new LensComponent('docks', 'passthrough-with-trashed-lens'), function ($browser) {
                        $browser->deleteResourceById(1);
                    });

            $this->assertEquals(1, Dock::withTrashed()->count());
        });
    }

    /**
     * @test
     */
    public function can_soft_delete_resources_using_checkboxes()
    {
        $this->seed();

        factory(Dock::class)->create();
        factory(Dock::class)->create();
        factory(Dock::class)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Lens('docks', 'passthrough-with-trashed-lens'))
                    ->within(new LensComponent('docks', 'passthrough-with-trashed-lens'), function ($browser) {
                        $browser->clickCheckboxForId(3)
                            ->clickCheckboxForId(2)
                            ->deleteSelected();
                    });
        });

            $this->assertEquals(2, Dock::onlyTrashed()->count());
    }

    /**
     * @test
     */
    public function can_restore_resources_using_checkboxes()
    {
        $this->seed();

        factory(Dock::class)->create();
        factory(Dock::class)->create(['deleted_at' => now()]);
        factory(Dock::class)->create(['deleted_at' => now()]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Lens('docks', 'passthrough-with-trashed-lens'))
                    ->within(new LensComponent('docks', 'passthrough-with-trashed-lens'), function ($browser) {
                        $browser->clickCheckboxForId(3)
                            ->clickCheckboxForId(2)
                            ->restoreSelected();
                    });
        });

        $this->assertEquals(3, Dock::count());
    }

    /**
     * @test
     */
    public function can_force_delete_resources_using_checkboxes()
    {
        $this->seed();

        factory(Dock::class)->create();
        factory(Dock::class)->create();
        factory(Dock::class)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Lens('docks', 'passthrough-with-trashed-lens'))
                    ->within(new LensComponent('docks', 'passthrough-with-trashed-lens'), function ($browser) {
                        $browser->clickCheckboxForId(3)
                            ->clickCheckboxForId(2)
                            ->forceDeleteSelected()
                            ->assertSeeResource(1)
                            ->assertDontSeeResource(2)
                            ->assertDontSeeResource(3);
                    });
        });
    }

    /**
     * @test
     */
    public function can_soft_delete_all_matching_resources()
    {
        $this->seed();

        factory(Dock::class)->create();
        factory(Dock::class)->create();
        factory(Dock::class)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Lens('docks', 'passthrough-with-trashed-lens'))
                    ->within(new LensComponent('docks', 'passthrough-with-trashed-lens'), function ($browser) {
                        $browser->applyFilter('Select First', '2');

                        $browser->selectAllMatching()
                                ->deleteSelected();
                    });

            $this->assertEquals(2, Dock::count());
        });
    }

    /**
     * @test
     */
    public function can_restore_all_matching_resources()
    {
        $this->seed();

        factory(Dock::class, 3)->create(['deleted_at' => now()]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Lens('docks', 'passthrough-with-trashed-lens'))
                    ->within(new LensComponent('docks', 'passthrough-with-trashed-lens'), function ($browser) {
                        $browser->applyFilter('Select First', '2');

                        $browser->selectAllMatching()
                            ->restoreSelected();
                    });

            $this->assertEquals(1, Dock::count());
            $this->assertEquals(2, Dock::onlyTrashed()->count());
        });
    }

    /**
     * @test
     */
    public function can_force_delete_all_matching_resources()
    {
        $this->seed();

        factory(Dock::class, 3)->create(['deleted_at' => now()]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Lens('docks', 'passthrough-with-trashed-lens'))
                    ->within(new LensComponent('docks', 'passthrough-with-trashed-lens'), function ($browser) {
                        $browser->applyFilter('Select First', '2');

                        $browser->selectAllMatching()
                            ->forceDeleteSelected();
                    });

            $this->assertEquals(0, Dock::count());
            $this->assertEquals(2, Dock::onlyTrashed()->count());
        });
    }

    public function soft_deleted_resource_is_still_viewable_with_proper_trash_state()
    {
        $this->seed();

        $dock = factory(Dock::class)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Index('docks'))
                    ->within(new IndexComponent('docks'), function ($browser) {
                        $browser->withTrashed()
                                ->deleteResourceById(1)
                                ->assertSeeResource(1);
                    });

            $this->assertEquals(1, Dock::withTrashed()->count());
        });
    }

    public function only_soft_deleted_resources_may_be_listed()
    {
        $this->seed();

        factory(Dock::class, 2)->create();
        Dock::find(2)->delete();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Index('docks'))
                    ->within(new IndexComponent('docks'), function ($browser) {
                        $browser->assertSeeResource(1)
                                ->assertDontSeeResource(2);

                        $browser->onlyTrashed()
                                ->assertDontSeeResource(1)
                                ->assertSeeResource(2);
                    });
        });
    }

    public function soft_deleted_resources_may_be_restored_via_row_icon()
    {
        $this->seed();

        factory(Dock::class)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Index('docks'))
                    ->within(new IndexComponent('docks'), function ($browser) {
                        $browser->withTrashed()
                                ->deleteResourceById(1)
                                ->restoreResourceById(1)
                                ->assertSeeResource(1);
                    });

            $this->assertEquals(1, Dock::count());
        });
    }
}
