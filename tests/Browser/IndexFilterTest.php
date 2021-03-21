<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\UserFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\UserIndex;
use Laravel\Nova\Tests\DuskTestCase;

class IndexFilterTest extends DuskTestCase
{
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
    public function filters_can_be_applied_to_resources()
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
    public function filters_can_be_deselected()
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
    public function date_filter_interactions_does_not_close_filter_dropdown()
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
}
