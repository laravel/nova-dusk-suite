<?php

namespace Tests\Browser;

use App\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
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
                    ->waitForUsers()
                    ->assertVisible('@users-1-row')
                    ->assertVisible('@users-2-row')
                    ->assertVisible('@users-3-row');
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
                    ->waitForUsers()
                    ->click('@create-users')
                    ->pause(1000)
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
                    ->waitForUsers()
                    ->click('@users-items-0-view-button')
                    ->pause(1000)
                    ->assertSee('User Details')
                    ->assertPathIs('/nova/resources/users/3');
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
                    ->waitForUsers()
                    ->click('@users-items-0-edit-button')
                    ->pause(1000)
                    ->assertSee('Edit User')
                    ->assertPathIs('/nova/resources/users/3/edit');
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
                    ->waitForUsers()
                    ->searchForUser('3')
                    ->assertMissing('@users-1-row')
                    ->assertMissing('@users-2-row')
                    ->assertVisible('@users-3-row');

            // Search For Single User By Name...
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\UserIndex)
                    ->waitForUsers()
                    ->searchForUser('Taylor')
                    ->assertVisible('@users-1-row')
                    ->assertMissing('@users-2-row')
                    ->assertMissing('@users-3-row');
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
                    ->waitForUsers()
                    ->assertSelectAllMatchingCount(3);

            $browser->searchForUser('Taylor')
                    ->assertSelectAllMatchingCount(1);
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
                    ->waitForUsers()
                    ->assertVisible('@users-50-row')
                    ->assertVisible('@users-26-row')
                    ->assertMissing('@users-25-row');

            $browser->click('@users-sort-id')
                    ->pause(500)
                    ->assertMissing('@users-50-row')
                    ->assertMissing('@users-26-row')
                    ->assertVisible('@users-25-row')
                    ->assertVisible('@users-1-row');
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
                    ->waitForUsers()
                    ->assertVisible('@users-50-row')
                    ->assertVisible('@users-26-row')
                    ->assertMissing('@users-25-row');

            $browser->click('@users-next')
                    ->pause(500)
                    ->assertMissing('@users-50-row')
                    ->assertMissing('@users-26-row')
                    ->assertVisible('@users-25-row')
                    ->assertVisible('@users-1-row');

            $browser->click('@users-previous')
                    ->pause(500)
                    ->assertVisible('@users-50-row')
                    ->assertVisible('@users-26-row')
                    ->assertMissing('@users-25-row')
                    ->assertMissing('@users-1-row');
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
                    ->waitForUsers()
                    ->setPerPage('50')
                    ->assertVisible('@users-50-row')
                    ->assertVisible('@users-25-row')
                    ->assertVisible('@users-1-row');
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
                    ->waitForUsers()
                    ->applyFilter('Select First', '1')
                    ->assertVisible('@users-1-row')
                    ->assertMissing('@users-2-row')
                    ->assertMissing('@users-3-row')
                    ->applyFilter('Select First', '2')
                    ->assertMissing('@users-1-row')
                    ->assertVisible('@users-2-row')
                    ->assertMissing('@users-3-row');
        });
    }

    public function test_filters_can_be_deselected()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\UserIndex)
                    ->waitForUsers()
                    ->applyFilter('Select First', '1')
                    ->assertVisible('@users-1-row')
                    ->assertMissing('@users-2-row')
                    ->assertMissing('@users-3-row')
                    ->applyFilter('Select First', '')
                    ->assertVisible('@users-1-row')
                    ->assertVisible('@users-2-row')
                    ->assertVisible('@users-3-row');
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
                    ->waitForUsers()
                    ->deleteUserAtIndex(0)
                    ->assertVisible('@users-1-row')
                    ->assertVisible('@users-2-row')
                    ->assertMissing('@users-3-row');
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
                    ->waitForUsers()
                    ->clickCheckboxAtIndex(0)
                    ->clickCheckboxAtIndex(1)
                    ->deleteSelected()
                    ->assertVisible('@users-1-row')
                    ->assertMissing('@users-2-row')
                    ->assertMissing('@users-3-row');
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
                    ->waitForUsers()
                    ->searchForUser('David')
                    ->selectAllMatching()
                    ->deleteSelected()
                    ->clearSearch()
                    ->assertVisible('@users-1-row')
                    ->assertVisible('@users-2-row')
                    ->assertMissing('@users-3-row');
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
                    ->waitForUsers()
                    ->clickCheckboxAtIndex(0)
                    ->clickCheckboxAtIndex(1)
                    ->runAction('mark-as-active');
        });

        $this->assertEquals(1, User::find(3)->active);
        $this->assertEquals(1, User::find(2)->active);
        $this->assertEquals(0, User::find(1)->active);
    }
}
