<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\SubscriberFactory;
use Database\Factories\UserFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Components\SelectAllDropdownComponent;
use Laravel\Nova\Testing\Browser\Pages\Index;
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
                        });
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
                        ->assertCheckboxChecked('[dusk="1-row"] [role="checkbox"]')
                        ->assertCheckboxChecked('[dusk="2-row"] [role="checkbox"]')
                        ->assertCheckboxChecked('[dusk="3-row"] [role="checkbox"]')
                        ->assertCheckboxChecked('[dusk="4-row"] [role="checkbox"]')
                        ->unselectAllOnCurrentPage()
                        ->assertNotChecked('[dusk="1-row"] [role="checkbox"]')
                        ->assertNotChecked('[dusk="2-row"] [role="checkbox"]')
                        ->assertNotChecked('[dusk="3-row"] [role="checkbox"]')
                        ->assertNotChecked('[dusk="4-row"] [role="checkbox"]')
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

    public function test_can_unselect_all_matching_on_single_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->selectAllMatching()
                        ->assertCheckboxChecked('[dusk="1-row"] [role="checkbox"]')
                        ->assertCheckboxChecked('[dusk="2-row"] [role="checkbox"]')
                        ->assertCheckboxChecked('[dusk="3-row"] [role="checkbox"]')
                        ->assertCheckboxChecked('[dusk="4-row"] [role="checkbox"]')
                        ->unselectAllMatching()
                        ->within(new SelectAllDropdownComponent(), function (Browser $browser) {
                            $browser->assertCheckboxIsIndeterminate();
                        });
                });

            $browser->blank();
        });
    }

    public function test_select_all_dropdown_and_checkboxes_are_missing_when_not_authorized_to_delete_a_resource()
    {
        SubscriberFactory::new()->times(5)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(3)
                ->visit(new Index('subscribers'))
                ->within(new IndexComponent('subscribers'), function ($browser) {
                    $browser->waitForTable()
                        ->assertMissing('@select-all-dropdown')
                        ->assertMissing('[dusk="1-row"] [role="checkbox"]')
                        ->assertMissing('[dusk="2-row"] [role="checkbox"]')
                        ->assertMissing('[dusk="3-row"] [role="checkbox"]')
                        ->assertMissing('[dusk="4-row"] [role="checkbox"]')
                        ->assertMissing('[dusk="5-row"] [role="checkbox"]');
                });
        });
    }
}
