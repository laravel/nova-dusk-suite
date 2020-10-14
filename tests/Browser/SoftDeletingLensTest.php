<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Dock;
use App\Models\User;
use Laravel\Dusk\Browser;
use Laravel\Nova\Tests\Browser\Components\LensComponent;
use Laravel\Nova\Tests\DuskTestCase;

class SoftDeletingLensTest extends DuskTestCase
{
    /**
     * @test
     */
    public function can_soft_delete_a_resource_via_resource_table_row_delete_icon()
    {
        $this->setupLaravel();

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
        $this->setupLaravel();

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
        $this->setupLaravel();

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
        $this->setupLaravel();

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
    // public function can_soft_delete_all_matching_resources()
    // {
    //     $this->setupLaravel();

    //     factory(Dock::class)->create();
    //     factory(Dock::class)->create();
    //     factory(Dock::class)->create();

    //     $this->browse(function (Browser $browser) {
    //         $browser->loginAs(User::find(1))
    //                 ->visit(new Pages\Lens('docks', 'passthrough-with-trashed-lens'))
    //                 ->within(new LensComponent('docks', 'passthrough-with-trashed-lens'), function ($browser) {
    //                     $browser->applyFilter('Select First', '2');

    //                     $browser->selectAllMatching()
    //                             ->deleteSelected();
    //                 });

    //         $this->assertEquals(2, Dock::count());
    //     });
    // }

    /**
     * @test
     */
    // public function can_restore_all_matching_resources()
    // {
    //     $this->setupLaravel();

    //     factory(Dock::class, 3)->create(['deleted_at' => now()]);

    //     $this->browse(function (Browser $browser) {
    //         $browser->loginAs(User::find(1))
    //                 ->visit(new Pages\Lens('docks', 'passthrough-with-trashed-lens'))
    //                 ->within(new LensComponent('docks', 'passthrough-with-trashed-lens'), function ($browser) {
    //                     $browser->applyFilter('Select First', '2');

    //                     $browser->selectAllMatching()
    //                         ->restoreSelected();
    //                 });

    //         $this->assertEquals(1, Dock::count());
    //         $this->assertEquals(2, Dock::onlyTrashed()->count());
    //     });
    // }

    /**
     * @test
     */
    // public function can_force_delete_all_matching_resources()
    // {
    //     $this->setupLaravel();

    //     factory(Dock::class, 3)->create(['deleted_at' => now()]);

    //     $this->browse(function (Browser $browser) {
    //         $browser->loginAs(User::find(1))
    //                 ->visit(new Pages\Lens('docks', 'passthrough-with-trashed-lens'))
    //                 ->within(new LensComponent('docks', 'passthrough-with-trashed-lens'), function ($browser) {
    //                     $browser->applyFilter('Select First', '2');

    //                     $browser->selectAllMatching()
    //                         ->forceDeleteSelected();
    //                 });

    //         $this->assertEquals(0, Dock::count());
    //         $this->assertEquals(2, Dock::onlyTrashed()->count());
    //     });
    // }

    /**
     * @test
     */
    public function soft_deleted_resources_may_be_restored_via_row_icon()
    {
        $this->setupLaravel();

        factory(Dock::class)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Lens('docks', 'passthrough-with-trashed-lens'))
                    ->within(new LensComponent('docks', 'passthrough-with-trashed-lens'), function ($browser) {
                        $browser->deleteResourceById(1)
                                ->restoreResourceById(1);
                    });

            $this->assertEquals(1, Dock::count());
        });
    }
}
