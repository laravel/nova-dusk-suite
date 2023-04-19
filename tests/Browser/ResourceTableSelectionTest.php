<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\UserFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Components\SelectAllDropdownComponent;
use Laravel\Nova\Testing\Browser\Pages\UserIndex;
use Laravel\Nova\Tests\DuskTestCase;

class ResourceTableSelectionTest extends DuskTestCase
{
    public function test_can_select_all_matching_on_single_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->within(new SelectAllDropdownComponent(), function (Browser $browser) {
                            $browser->assertCheckboxIsNotChecked()
                                ->assertSelectAllOnCurrentPageNotChecked()
                                ->assertSelectAllMatchingNotChecked();
                        })
                        ->clickCheckboxForId(4)
                        ->clickCheckboxForId(3)
                        ->clickCheckboxForId(2)
                        ->clickCheckboxForId(1)
                        ->within(new SelectAllDropdownComponent(), function (Browser $browser) {
                            $browser->assertCheckboxIsIndeterminate()
                                ->assertSelectAllOnCurrentPageChecked()
                                ->assertSelectAllMatchingNotChecked()
                                ->assertSelectedCount(4);
                        });
                });

            $browser->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->within(new SelectAllDropdownComponent(), function (Browser $browser) {
                            $browser->selectAllOnCurrentPage()
                                ->assertCheckboxIsIndeterminate()
                                ->assertSelectAllOnCurrentPageChecked()
                                ->assertSelectAllMatchingNotChecked()
                                ->assertSelectedCount(4);
                        })->closeCurrentDropdown();
                });

            $browser->blank();
        });
    }

    public function test_can_select_all_matching_on_multiple_page()
    {
        UserFactory::new()->times(50)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->within(new SelectAllDropdownComponent(), function (Browser $browser) {
                            $browser->selectAllOnCurrentPage()
                                ->assertCheckboxIsIndeterminate()
                                ->assertSelectAllOnCurrentPageChecked()
                                ->assertSelectAllMatchingNotChecked()
                                ->assertSelectedCount(25);
                        });
                });

            $browser->blank();
        });
    }

    public function test_can_unselect_all_on_single_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->selectAllOnCurrentPage()
                        ->assertChecked('[dusk="1-row"] input.checkbox')
                        ->assertChecked('[dusk="2-row"] input.checkbox')
                        ->assertChecked('[dusk="3-row"] input.checkbox')
                        ->assertChecked('[dusk="4-row"] input.checkbox')
                        ->unselectAllOnCurrentPage()
                        ->assertNotChecked('[dusk="1-row"] input.checkbox')
                        ->assertNotChecked('[dusk="2-row"] input.checkbox')
                        ->assertNotChecked('[dusk="3-row"] input.checkbox')
                        ->assertNotChecked('[dusk="4-row"] input.checkbox')
                        ->within(new SelectAllDropdownComponent(), function (Browser $browser) {
                            $browser->assertCheckboxIsNotChecked()
                                ->assertSelectAllOnCurrentPageNotChecked()
                                ->assertSelectAllMatchingNotChecked()
                                ->assertSelectAllMatchingCount(4);
                        });
                });

            $browser->blank();
        });
    }

    public function test_can_unselect_matching_all_on_single_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->selectAllMatching()
                        ->assertChecked('[dusk="1-row"] input.checkbox')
                        ->assertChecked('[dusk="2-row"] input.checkbox')
                        ->assertChecked('[dusk="3-row"] input.checkbox')
                        ->assertChecked('[dusk="4-row"] input.checkbox')
                        ->unselectAllMatching()
                        ->assertNotChecked('[dusk="1-row"] input.checkbox')
                        ->assertNotChecked('[dusk="2-row"] input.checkbox')
                        ->assertNotChecked('[dusk="3-row"] input.checkbox')
                        ->assertNotChecked('[dusk="4-row"] input.checkbox')
                        ->within(new SelectAllDropdownComponent(), function (Browser $browser) {
                            $browser->assertCheckboxIsNotChecked();
                        });
                });

            $browser->blank();
        });
    }
}
