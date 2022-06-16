<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\UserFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
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
                            ->within('@select-all-dropdown', function (Browser $browser) {
                                $browser->assertNotChecked('input[type="checkbox"]')
                                    ->click('')
                                    ->elsewhereWhenAvailable('@select-all-button', function (Browser $browser) {
                                        $browser->assertNotChecked('input[type="checkbox"]');
                                    })
                                    ->elsewhereWhenAvailable('@select-all-matching-button', function (Browser $browser) {
                                        $browser->assertNotChecked('input[type="checkbox"]');
                                    });
                            })
                            ->closeCurrentDropdown()
                            ->clickCheckboxForId(4)
                            ->clickCheckboxForId(3)
                            ->clickCheckboxForId(2)
                            ->clickCheckboxForId(1)
                            ->within('@select-all-dropdown', function (Browser $browser) {
                                $browser->assertChecked('input[type="checkbox"]')
                                    ->click('')
                                    ->elsewhereWhenAvailable('@select-all-button', function (Browser $browser) {
                                        $browser->assertChecked('input[type="checkbox"]');
                                    })
                                    ->elsewhereWhenAvailable('@select-all-matching-button', function (Browser $browser) {
                                        $browser->assertNotChecked('input[type="checkbox"]')->assertSeeIn('span:nth-child(2)', 4);
                                    });
                            })->closeCurrentDropdown();
                    });

            $browser->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable()
                            ->selectAllOnCurrentPage()
                            ->within('@select-all-dropdown', function (Browser $browser) {
                                $browser->assertChecked('input[type="checkbox"]')
                                    ->click('')
                                    ->elsewhereWhenAvailable('@select-all-button', function (Browser $browser) {
                                        $browser->assertChecked('input[type="checkbox"]');
                                    })
                                    ->elsewhereWhenAvailable('@select-all-matching-button', function (Browser $browser) {
                                        $browser->assertNotChecked('input[type="checkbox"]')->assertSeeIn('span:nth-child(2)', 4);
                                    });
                            })->closeCurrentDropdown();
                    });

            $browser->blank();
        });
    }

    public function test_can_select_all_matching_on_multiple_page()
    {
        UserFactory::new()->times(50)->create();

        $this->browse(function (Browser $browser) {
            $browser->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable()
                            ->selectAllOnCurrentPage()
                            ->within('@select-all-dropdown', function (Browser $browser) {
                                $browser->assertNotChecked('input[type="checkbox"]')
                                    ->click('')
                                    ->elsewhereWhenAvailable('@select-all-button', function (Browser $browser) {
                                        $browser->assertChecked('input[type="checkbox"]');
                                    })
                                    ->elsewhereWhenAvailable('@select-all-matching-button', function (Browser $browser) {
                                        $browser->assertNotChecked('input[type="checkbox"]')->assertSeeIn('span:nth-child(2)', 4);
                                    });
                            })->closeCurrentDropdown();
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
                            ->within('@select-all-dropdown', function (Browser $browser) {
                                $browser->assertNotChecked('input[type="checkbox"]')
                                    ->click('')
                                    ->elsewhereWhenAvailable('@select-all-button', function (Browser $browser) {
                                        $browser->assertNotChecked('input[type="checkbox"]');
                                    })
                                    ->elsewhereWhenAvailable('@select-all-matching-button', function (Browser $browser) {
                                        $browser->assertNotChecked('input[type="checkbox"]')->assertSeeIn('span:nth-child(2)', 4);
                                    });
                            })->closeCurrentDropdown();
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
                            ->assertChecked('[dusk="1-row"] input.checkbox')
                            ->assertChecked('[dusk="2-row"] input.checkbox')
                            ->assertChecked('[dusk="3-row"] input.checkbox')
                            ->assertChecked('[dusk="4-row"] input.checkbox')
                            ->within('@select-all-dropdown', function (Browser $browser) {
                                $browser->assertChecked('input[type="checkbox"]')
                                    ->click('')
                                    ->elsewhereWhenAvailable('@select-all-button', function (Browser $browser) {
                                        $browser->assertChecked('input[type="checkbox"]');
                                    })
                                    ->elsewhereWhenAvailable('@select-all-matching-button', function (Browser $browser) {
                                        $browser->assertNotChecked('input[type="checkbox"]');
                                    });
                            })->closeCurrentDropdown();
                    });

            $browser->blank();
        });
    }
}
