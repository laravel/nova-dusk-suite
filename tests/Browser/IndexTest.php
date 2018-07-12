<?php

namespace Tests\Browser;

use App\Dock;
use App\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\IndexComponent;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class IndexTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function resource_index_can_be_viewed()
    {
        $this->seed();

        $users = User::find([1, 2, 3]);

        $this->browse(function (Browser $browser) use ($users) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->assertSeeResource(1)
                                ->assertSeeResource(2)
                                ->assertSeeResource(3);
                    });
        });
    }

    /**
     * @test
     */
    public function can_navigate_to_create_resource_screen()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->click('@create-button');
                    })
                    ->pause(250)
                    ->assertSee('Create & Add Another')
                    ->assertSee('Create User');
        });
    }

    /**
     * @test
     */
    public function can_navigate_to_detail_screen()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->click('@1-view-button');
                    })
                    ->pause(1000)
                    ->assertSee('User Details')
                    ->assertPathIs('/nova/resources/users/1');
        });
    }

    /**
     * @test
     */
    public function can_navigate_to_edit_screen()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->click('@1-edit-button');
                    })
                    ->pause(1000)
                    ->assertSee('Edit User')
                    ->assertPathIs('/nova/resources/users/1/edit');
        });
    }

    /**
     * @test
     */
    public function resources_can_be_searched()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            // Search For Single User By ID...
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->searchFor('3')
                                ->assertDontSeeResource(1)
                                ->assertDontSeeResource(2)
                                ->assertSeeResource(3);
                    });

            // Search For Single User By Name...
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->searchFor('Taylor')
                                ->assertSeeResource(1)
                                ->assertDontSeeResource(2)
                                ->assertDontSeeResource(3);
                    });
        });
    }

    /**
     * @test
     */
    public function test_correct_select_all_matching_count_is_displayed()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->assertSelectAllMatchingCount(3)
                                ->click('')
                                ->searchFor('Taylor')
                                ->assertSelectAllMatchingCount(1);
                    });
        });
    }

    /**
     * @test
     */
    public function resources_can_be_sorted_by_id()
    {
        factory(User::class, 50)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->assertSeeResource(50)
                                ->assertSeeResource(26)
                                ->assertDontSeeResource(25);

                        $browser->sortBy('id')
                                ->assertDontSeeResource(50)
                                ->assertDontSeeResource(26)
                                ->assertSeeResource(25)
                                ->assertSeeResource(1);
                    });
        });
    }

    /**
     * @test
     */
    public function resources_can_be_paginated()
    {
        factory(User::class, 50)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->assertSeeResource(50)
                                ->assertSeeResource(26)
                                ->assertDontSeeResource(25);

                        $browser->nextPage()
                                ->assertDontSeeResource(50)
                                ->assertDontSeeResource(26)
                                ->assertSeeResource(25)
                                ->assertSeeResource(1);

                        $browser->previousPage()
                                ->assertSeeResource(50)
                                ->assertSeeResource(26)
                                ->assertDontSeeResource(25)
                                ->assertDontSeeResource(1);
                    });
        });
    }

    /**
     * @test
     */
    public function number_of_resources_displayed_per_page_can_be_changed()
    {
        factory(User::class, 50)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->setPerPage('50')
                                ->assertSeeResource(50)
                                ->assertSeeResource(25)
                                ->assertSeeResource(1);
                    });
        });
    }

    /**
     * @test
     */
    public function test_filters_can_be_applied_to_resources()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->applyFilter('Select First', '1')
                            ->assertSeeResource(1)
                            ->assertDontSeeResource(2)
                            ->assertDontSeeResource(3)
                            ->applyFilter('Select First', '2')
                            ->assertDontSeeResource(1)
                            ->assertSeeResource(2)
                            ->assertDontSeeResource(3);
                    });
        });
    }

    public function test_filters_can_be_deselected()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->applyFilter('Select First', '1')
                            ->assertSeeResource(1)
                            ->assertDontSeeResource(2)
                            ->assertDontSeeResource(3)
                            ->applyFilter('Select First', '')
                            ->assertSeeResource(1)
                            ->assertSeeResource(2)
                            ->assertSeeResource(3);
                    });
        });
    }

    /**
     * @test
     */
    public function can_delete_a_resource_via_resource_table_row_delete_icon()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->deleteResourceById(3)
                                ->assertSeeResource(1)
                                ->assertSeeResource(2)
                                ->assertDontSeeResource(3);
                    });
        });
    }

    /**
     * @test
     */
    public function can_soft_delete_a_resource_via_resource_table_row_delete_icon()
    {
        $this->seed();

        $dock = factory(Dock::class)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Index('docks'))
                    ->within(new IndexComponent('docks'), function ($browser) {
                        $browser->deleteResourceById(1)
                                ->assertDontSeeResource(1);
                    });

            $this->assertEquals(1, Dock::withTrashed()->count());
        });
    }

    /**
     * @test
     */
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

    /**
     * @test
     */
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

    /**
     * @test
     */
    public function can_delete_resources_using_checkboxes()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->clickCheckboxForId(3)
                            ->clickCheckboxForId(2)
                            ->deleteSelected()
                            ->assertSeeResource(1)
                            ->assertDontSeeResource(2)
                            ->assertDontSeeResource(3);
                    });
        });
    }

    /**
     * @test
     */
    public function can_delete_all_matching_resources()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->searchFor('David')
                            ->selectAllMatching()
                            ->deleteSelected()
                            ->clearSearch()
                            ->assertSeeResource(1)
                            ->assertSeeResource(2)
                            ->assertDontSeeResource(3);
                    });
        });
    }

    /**
     * @test
     */
    public function can_run_actions_on_selected_resources()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->clickCheckboxForId(3)
                            ->clickCheckboxForId(2)
                            ->runAction('mark-as-active');
                    });
        });

        $this->assertEquals(0, User::find(1)->active);
        $this->assertEquals(1, User::find(2)->active);
        $this->assertEquals(1, User::find(3)->active);
    }
}
