<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\DockFactory;
use Database\Factories\UserFactory;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\BreadcrumbComponent;
use Laravel\Nova\Testing\Browser\Components\LensComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\Lens;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Tests\DuskTestCase;

class LensTest extends DuskTestCase
{
    public function test_resource_lens_can_be_viewed()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Lens('users', 'passthrough-lens'))
                ->within(new BreadcrumbComponent(), function ($browser) {
                    $browser->assertSee('Resources')
                        ->assertSeeLink('Users')
                        ->assertCurrentPageTitle('Passthrough Lens');
                })
                ->within(new LensComponent('users', 'passthrough-lens'), function ($browser) {
                    $browser->waitForTable()
                        ->assertSeeResource(1)
                        ->assertSeeResource(2)
                        ->assertSeeResource(3);
                })
                ->assertTitle('Nova Site - Passthrough Lens');

            $browser->blank();
        });
    }

    public function test_can_navigate_to_detail_screen()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Lens('users', 'passthrough-lens'))
                ->within(new LensComponent('users', 'passthrough-lens'), function ($browser) {
                    $browser->waitForTable()
                        ->click('@1-view-button');
                })
                ->on(new Detail('users', 1))
                ->assertSeeIn('h1', 'User Details');

            $browser->blank();
        });
    }

    public function test_can_navigate_to_edit_screen()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Lens('users', 'passthrough-lens'))
                ->within(new LensComponent('users', 'passthrough-lens'), function ($browser) {
                    $browser->waitForTable()
                        ->click('@1-edit-button');
                })
                ->on(new Update('users', 1))
                ->assertSeeIn('h1', 'Update User');

            $browser->blank();
        });
    }

    public function test_can_navigate_to_different_lens_screen()
    {
        $dock = DockFactory::new()->create([
            'name' => 'Active Dock ('.Str::random(6).')',
            'active' => true,
        ]);
        $trashedDock = DockFactory::new()->create([
            'name' => 'Inactive Dock ('.Str::random(6).')',
            'active' => true,
            'deleted_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($dock, $trashedDock) {
            $browser->loginAs(1)
                ->visit(new Lens('docks', 'passthrough-lens'))
                ->within(new LensComponent('docks', 'passthrough-lens'), function ($browser) use ($dock, $trashedDock) {
                    $browser->waitForTextIn('h1', 'Passthrough Lens')
                        ->waitForTable()
                        ->assertSee($dock->name)
                        ->assertDontSee($trashedDock->name)
                        ->selectAllMatching()
                        ->assertPresent('@action-select')
                        ->assertSelectHasOptions('@action-select', ['mark-as-active']);
                });

            $browser->script([
                'Nova.visit("/resources/docks/lens/passthrough-with-trashed-lens");',
            ]);

            $browser->on(new Lens('docks', 'passthrough-with-trashed-lens'))
                ->within(new LensComponent('docks', 'passthrough-with-trashed-lens'), function ($browser) use ($dock, $trashedDock) {
                    $browser->assertSeeIn('h1', 'Passthrough With Trashed Lens')
                        ->waitForTable()
                        ->assertSee($dock->name)
                        ->assertSee($trashedDock->name)
                        ->selectAllMatching()
                        ->assertMissing('@action-select');
                });

            $browser->blank();
        });
    }

    public function test_correct_select_all_matching_count_is_displayed()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Lens('users', 'passthrough-lens'))
                ->within(new LensComponent('users', 'passthrough-lens'), function ($browser) {
                    $browser->waitForTable()
                        ->assertSelectAllMatchingCount(4)
                        ->closeCurrentDropdown()
                        ->selectFilter('Select First', '1')
                        ->assertSelectAllMatchingCount(1);
                });

            $browser->blank();
        });
    }

    public function test_resources_can_be_sorted_by_id()
    {
        UserFactory::new()->times(50)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Lens('users', 'passthrough-lens'))
                ->within(new LensComponent('users', 'passthrough-lens'), function ($browser) {
                    $browser->waitForTable()
                        ->assertSeeResource(1)
                        ->assertSeeResource(25)
                        ->assertDontSeeResource(26);

                    $browser->sortBy('id')
                        ->sortBy('id')
                        ->assertSeeResource(50)
                        ->assertSeeResource(30)
                        ->assertDontSeeResource(26)
                        ->assertDontSeeResource(1);
                });

            $browser->blank();
        });
    }

    public function test_resources_can_be_paginated()
    {
        UserFactory::new()->times(50)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Lens('users', 'passthrough-lens'))
                ->within(new LensComponent('users', 'passthrough-lens'), function ($browser) {
                    $browser->waitForTable()
                        ->assertSeeResource(1)
                        ->assertSeeResource(25)
                        ->assertDontSeeResource(26);

                    $browser->nextPage()
                        ->assertDontSeeResource(1)
                        ->assertDontSeeResource(25)
                        ->assertSeeResource(26)
                        ->assertSeeResource(50);

                    $browser->previousPage()
                        ->assertSeeResource(1)
                        ->assertSeeResource(25)
                        ->assertDontSeeResource(26)
                        ->assertDontSeeResource(50);
                });

            $browser->blank();
        });
    }

    public function test_can_delete_a_resource_via_resource_table_row_delete_icon()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Lens('users', 'passthrough-lens'))
                ->within(new LensComponent('users', 'passthrough-lens'), function ($browser) {
                    $browser->waitForTable()
                        ->deleteResourceById(3)
                        ->waitForTable()
                        ->assertSeeResource(1)
                        ->assertSeeResource(2)
                        ->assertDontSeeResource(3);
                });

            $browser->blank();
        });
    }

    public function test_can_delete_resources_using_checkboxes()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Lens('users', 'passthrough-lens'))
                ->within(new LensComponent('users', 'passthrough-lens'), function ($browser) {
                    $browser->waitForTable()
                        ->clickCheckboxForId(3)
                        ->clickCheckboxForId(2)
                        ->pause(175)
                        ->deleteSelected()
                        ->waitForTable()
                        ->assertSeeResource(1)
                        ->assertDontSeeResource(2)
                        ->assertDontSeeResource(3);
                });

            $browser->blank();
        });
    }

    public function test_can_delete_all_matching_resources()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Lens('users', 'passthrough-lens'))
                ->within(new LensComponent('users', 'passthrough-lens'), function ($browser) {
                    $browser->waitForTable()
                        ->selectFilter('Select First', '3')
                        ->selectAllMatching()
                        ->deleteSelected()
                        ->selectFilter('Select First', '')
                        ->waitForTable()
                        ->assertSeeResource(1)
                        ->assertSeeResource(2)
                        ->assertDontSeeResource(3);
                });

            $browser->blank();
        });
    }
}
