<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\PostFactory;
use Database\Factories\UserFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Index;
use Laravel\Nova\Testing\Browser\Pages\UserIndex;
use Laravel\Nova\Tests\DuskTestCase;

class IndexTest extends DuskTestCase
{
    /**
     * @test
     */
    public function resource_index_can_be_viewed()
    {
        $users = User::find([1, 2, 3]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                                ->assertSeeResource(1)
                                ->assertSeeResource(2)
                                ->assertSeeResource(3)
                                ->assertSee('1-4 of 4');
                    })
                    ->assertTitle('Users | Nova Dusk Suite');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function resource_index_cant_be_viewed_on_invalid_resource()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Index('foobar'))
                    ->waitForText('404', 15)
                    ->assertPathIs('/nova/404');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_navigate_to_create_resource_screen()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->click('@create-button');
                    })
                    ->waitForTextIn('h1', 'Create User', 25)
                    ->assertSee('Create & Add Another')
                    ->assertSee('Create User');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_navigate_to_different_index_screen()
    {
        $post = PostFactory::new()->create();

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(User::find(1))
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTextIn('h1', 'Users', 25)
                            ->assertSee('Mohamed Said')
                            ->assertSee('David Hemphill');
                    });

            $browser->script([
                'Nova.app.$router.push({ name: "index", params: { resourceName: "posts" }});',
            ]);

            $browser->waitForTextIn('h1', 'User Post', 25)
                    ->within(new IndexComponent('posts'), function ($browser) use ($post) {
                        $browser->assertSee($post->title)
                            ->assertDontSee('Mohamed Said')
                            ->assertDontSee('David Hemphill');
                    })->assertPathIs('/nova/resources/posts');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_navigate_to_detail_screen()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                                ->click('@1-view-button');
                    })
                    ->waitForText('User Details', 25)
                    ->assertSee('User Details')
                    ->assertPathIs('/nova/resources/users/1');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_navigate_to_edit_screen()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                                ->click('@1-edit-button');
                    })
                    ->waitForText('Update User', 25)
                    ->assertSee('Update User')
                    ->assertPathIs('/nova/resources/users/1/edit');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function resources_can_be_searched()
    {
        $this->browse(function (Browser $browser) {
            // Search For Single User By ID...
            $browser->loginAs(User::find(1))
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                                ->searchFor('3')
                                ->assertDontSeeResource(1)
                                ->assertDontSeeResource(2)
                                ->assertSeeResource(3)
                                ->assertSee('1-1 of 1');
                    });

            // Search For Single User By Name...
            $browser->loginAs(User::find(1))
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                                ->searchFor('Taylor')
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
    public function resources_search_query_will_reset_on_revisit()
    {
        $this->browse(function (Browser $browser) {
            // Search For Single User By ID...
            $browser->loginAs(User::find(1))
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                                ->searchFor('3')
                                ->assertDontSeeResource(1)
                                ->assertDontSeeResource(2)
                                ->assertSeeResource(3)
                                ->assertDontSeeResource(4)
                                ->assertValue('@search', '3');
                    })
                    ->click('@users-resource-link')
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                                ->assertValue('@search', '')
                                ->assertSeeResource(1)
                                ->assertSeeResource(2)
                                ->assertSeeResource(3)
                                ->assertSeeResource(4);
                    });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function test_correct_select_all_matching_count_is_displayed()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                                ->assertSee('1-4 of 4')
                                ->assertSelectAllMatchingCount(4)
                                ->click('')
                                ->searchFor('Taylor')
                                ->assertSelectAllMatchingCount(1)
                                ->assertSee('1-1 of 1');
                    });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function resources_can_be_sorted_by_id()
    {
        UserFactory::new()->times(50)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                                ->assertSeeResource(50)
                                ->assertSeeResource(36)
                                ->assertDontSeeResource(25)
                                ->assertSee('1-25 of 54');

                        $browser->sortBy('id')
                                ->assertDontSeeResource(50)
                                ->assertDontSeeResource(26)
                                ->assertSeeResource(25)
                                ->assertSeeResource(1)
                                ->assertSee('1-25 of 54');
                    });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function resources_can_be_resorted_by_different_field_default_to_ascending_first()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                            ->assertSee('1-4 of 4')
                            ->assertSeeIn('table > tbody > tr:first-child', 'Laravel Nova');

                        $browser->sortBy('name')
                            ->assertSeeIn('table > tbody > tr:first-child', 'David Hemphill')
                            ->sortBy('name')
                            ->assertSeeIn('table > tbody > tr:first-child', 'Taylor Otwell')
                            ->sortBy('email')
                            ->assertSeeIn('table > tbody > tr:first-child', 'David Hemphill');
                    });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function resources_can_be_paginated()
    {
        UserFactory::new()->times(50)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                                ->assertSeeResource(50)
                                ->assertSeeResource(30)
                                ->assertDontSeeResource(25)
                                ->assertSee('1-25 of 54');

                        $browser->nextPage()
                                ->assertDontSeeResource(50)
                                ->assertDontSeeResource(30)
                                ->assertSeeResource(25)
                                ->assertDontSeeResource(1)
                                ->assertSee('26-50 of 54');

                        $browser->previousPage()
                                ->assertSeeResource(50)
                                ->assertSeeResource(30)
                                ->assertDontSeeResource(25)
                                ->assertDontSeeResource(1)
                                ->assertSee('1-25 of 54');
                    });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function number_of_resources_displayed_per_page_can_be_changed()
    {
        UserFactory::new()->times(50)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                                ->setPerPage('50')
                                ->pause(1500)
                                ->assertSeeResource(50)
                                ->assertSeeResource(25)
                                ->assertDontSeeResource(1)
                                ->assertSee('1-50 of 54');
                    });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function number_of_resources_displayed_per_page_is_saved_in_query_params()
    {
        UserFactory::new()->times(50)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                                ->setPerPage('50')
                                ->pause(1500)
                                ->assertSeeResource(50)
                                ->assertSeeResource(25)
                                ->assertDontSeeResource(1)
                                ->assertSee('1-50 of 54');
                    })
                    ->refresh()
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                                ->assertSeeResource(50)
                                ->assertSeeResource(25)
                                ->assertDontSeeResource(1)
                                ->assertSee('1-50 of 54');
                    });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function test_filters_can_be_applied_to_resources()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                            ->applyFilter('Select First', '1')
                            ->pause(1500)
                            ->assertSeeResource(1)
                            ->assertDontSeeResource(2)
                            ->assertDontSeeResource(3)
                            ->assertSee('1-1 of 1')
                            ->applyFilter('Select First', '2')
                            ->pause(1500)
                            ->assertDontSeeResource(1)
                            ->assertSeeResource(2)
                            ->assertDontSeeResource(3)
                            ->assertSee('1-1 of 1');
                    });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function test_filters_can_be_deselected()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                            ->applyFilter('Select First', '1')
                            ->pause(1500)
                            ->assertSeeResource(1)
                            ->assertDontSeeResource(2)
                            ->assertDontSeeResource(3)
                            ->assertSee('1-1 of 1')
                            ->applyFilter('Select First', '')
                            ->pause(1500)
                            ->assertSeeResource(1)
                            ->assertSeeResource(2)
                            ->assertSeeResource(3)
                            ->assertSee('1-4 of 4');
                    });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function test_date_filter_interactions_does_not_close_filter_dropdown()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                            ->assertMissing('@filter-per-page')
                            ->click('@filter-selector')
                            ->pause(500)
                            ->elsewhere('', function ($browser) {
                                $browser->assertVisible('@filter-per-page')
                                    ->type('[dusk="date-filter"] + input', '')
                                    ->elsewhere('', function ($browser) {
                                        $browser->click('.flatpickr-prev-month');
                                    })
                                    ->assertVisible('@filter-per-page');

                                $browser->click('@global-search')
                                    ->assertMissing('@filter-per-page');
                            });
                    });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_delete_a_resource_via_resource_table_row_delete_icon()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                                ->deleteResourceById(3)
                                ->pause(1500)
                                ->assertSeeResource(1)
                                ->assertSeeResource(2)
                                ->assertDontSeeResource(3)
                                ->assertSee('1-3 of 3');
                    });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_delete_resources_using_checkboxes()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                            ->clickCheckboxForId(3)
                            ->clickCheckboxForId(2)
                            ->deleteSelected()
                            ->assertSeeResource(1)
                            ->assertDontSeeResource(2)
                            ->assertDontSeeResource(3)
                            ->assertSee('1-2 of 2');
                    })
                    ->assertPathIs('/nova/resources/users');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_delete_all_matching_resources()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                            ->searchFor('David')
                            ->selectAllMatching()
                            ->deleteSelected()
                            ->clearSearch()
                            ->assertSeeResource(1)
                            ->assertSeeResource(2)
                            ->assertDontSeeResource(3)
                            ->assertSee('1-3 of 3');
                    })
                    ->assertPathIs('/nova/resources/users');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_run_actions_on_selected_resources()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                            ->clickCheckboxForId(3)
                            ->clickCheckboxForId(2)
                            ->runAction('mark-as-active');
                    });

            $this->assertEquals(0, User::find(1)->active);
            $this->assertEquals(1, User::find(2)->active);
            $this->assertEquals(1, User::find(3)->active);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_run_table_row_actions_on_selected_resources()
    {
        User::whereIn('id', [2, 3, 4])->update(['active' => true]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                            ->assertDontSeeIn('@1-row', 'Mark As Inactive')
                            ->assertSeeIn('@2-row', 'Mark As Inactive')
                            ->runInlineAction(2, 'mark-as-inactive');
                    });

            $this->assertEquals(0, User::find(1)->active);
            $this->assertEquals(0, User::find(2)->active);
            $this->assertEquals(1, User::find(3)->active);

            $browser->blank();
        });
    }
}
