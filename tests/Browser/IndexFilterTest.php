<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\UserFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\UserIndex;
use Laravel\Nova\Tests\DuskTestCase;

class IndexFilterTest extends DuskTestCase
{
    public function test_number_of_resources_displayed_per_page_can_be_changed()
    {
        UserFactory::new()->times(50)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->setPerPage('50')
                        ->waitForTable()
                        ->assertSeeResource(50)
                        ->assertSeeResource(25)
                        ->assertDontSeeResource(1)
                        ->assertSee('1-50 of 54');
                });

            $browser->blank();
        });
    }

    public function test_number_of_resources_displayed_per_page_is_saved_in_query_params()
    {
        UserFactory::new()->times(50)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->setPerPage('50')
                        ->waitForTable()
                        ->assertSeeResource(50)
                        ->assertSeeResource(25)
                        ->assertDontSeeResource(1)
                        ->assertSee('1-50 of 54');
                })
                ->refresh()
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->assertSeeResource(50)
                        ->assertSeeResource(25)
                        ->assertDontSeeResource(1)
                        ->assertSee('1-50 of 54');
                });

            $browser->blank();
        });
    }

    public function test_filters_can_be_applied_to_resources()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->selectFilter('Select First', '1')
                        ->waitForTable()
                        ->assertSeeResource(1)
                        ->assertDontSeeResource(2)
                        ->assertDontSeeResource(3)
                        ->assertSee('1-1 of 1')
                        ->selectFilter('Select First', '2')
                        ->waitForTable()
                        ->assertDontSeeResource(1)
                        ->assertSeeResource(2)
                        ->assertDontSeeResource(3)
                        ->assertSee('1-1 of 1');
                });

            $browser->blank();
        });
    }

    public function test_filters_can_be_deselected()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->selectFilter('Select First', '1')
                        ->waitForTable()
                        ->assertSeeResource(1)
                        ->assertDontSeeResource(2)
                        ->assertDontSeeResource(3)
                        ->assertSee('1-1 of 1')
                        ->selectFilter('Select First', '')
                        ->waitForTable()
                        ->assertSeeResource(1)
                        ->assertSeeResource(2)
                        ->assertSeeResource(3)
                        ->assertSee('1-4 of 4');
                });

            $browser->blank();
        });
    }

    public function test_filters_can_be_applied_will_reset_pagination_to_resources()
    {
        UserFactory::new()->times(25)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->assertSee('1-25 of 29')
                        ->nextPage()
                        ->assertQueryStringHas('users_page', 2)
                        ->assertSee('26-29 of 29')
                        ->selectFilter('Select First', '1')
                        ->waitForTable()
                        ->assertSeeResource(1)
                        ->assertDontSeeResource(2)
                        ->assertDontSeeResource(3)
                        ->assertSee('1-1 of 1')
                        ->assertQueryStringHas('users_page', 1);
                });

            $browser->blank();
        });
    }

    public function test_date_filter_can_be_selected_and_reset()
    {
        $this->browse(function (Browser $browser) {
            $user = UserFactory::new()->times(3)->create([
                'created_at' => '2022-01-01 00:00:00',
                'updated_at' => '2022-01-01 00:00:00',
            ]);

            $browser->loginAs(1)
                ->visit(new UserIndex([
                    'users_filter' => 'W3siQXBwXFxOb3ZhXFxGaWx0ZXJzXFxXaXRoUG9zdHMiOiIifSx7IkFwcFxcTm92YVxcRmlsdGVyc1xcU2VsZWN0Rmlyc3QiOiIifSx7IkFwcFxcTm92YVxcRmlsdGVyc1xcQ3JlYXRlZCI6IjIwMjItMDEtMDEifSx7IlRleHQ6bmFtZSI6IiJ9LHsiQm9vbGVhbjphY3RpdmUiOiIifSx7IkJvb2xlYW5Hcm91cDpwZXJtaXNzaW9ucyI6IiJ9LHsiRGF0ZVRpbWU6Y3JlYXRlZF9hdCI6W251bGwsbnVsbF19LHsicmVzb3VyY2U6cm9sZXM6cm9sZXMiOiIifSx7InJlc291cmNlOmJvb2tzOmdpZnRCb29rcyI6IiJ9XQ==',
                ]))->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->assertDontSeeResource(1)
                        ->assertDontSeeResource(2)
                        ->assertDontSeeResource(3)
                        ->assertDontSeeResource(4)
                        ->assertMissing('@filter-per-page')
                        ->click('@filter-selector')
                        ->pause(500)
                        ->elsewhere('', function ($browser) {
                            $browser->assertVisible('@filter-per-page')
                                ->type('input[dusk="Created-date-filter"]', '')
                                ->assertVisible('@filter-per-page');
                        })
                        ->waitForTable()
                        ->assertSeeResource(1)
                        ->assertSeeResource(2)
                        ->assertSeeResource(3)
                        ->assertSeeResource(4)
                        ->assertSeeResource(5)
                        ->assertSeeResource(6)
                        ->assertSeeResource(7)
                        ->elsewhere('', function ($browser) {
                            $browser->assertVisible('@filter-per-page');
                        })
                        ->closeCurrentDropdown()
                        ->assertMissing('@filter-per-page');
                });

            $browser->blank();
        });
    }

    /**
     * @dataProvider userResourceUrlWithFilterApplied
     */
    public function test_filters_can_be_applied_to_resources_received_from_url($url)
    {
        $this->browse(function (Browser $browser) use ($url) {
            $browser->loginAs(1)
                ->visit($url)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->assertDontSeeResource(1)
                        ->assertDontSeeResource(2)
                        ->assertSeeResource(3)
                        ->assertDontSeeResource(4);
                });

            $browser->blank();
        });
    }

    public function test_date_filter_interactions_does_not_close_filter_dropdown()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->assertMissing('@filter-per-page')
                        ->click('@filter-selector')
                        ->pause(500)
                        ->elsewhere('', function ($browser) {
                            $browser->assertVisible('@filter-per-page')
                                ->type('input[dusk="Created-date-filter"]', '')
                                ->assertVisible('@filter-per-page');
                        })
                        ->closeCurrentDropdown()
                        ->assertMissing('@filter-per-page');
                });

            $browser->blank();
        });
    }

    public static function userResourceUrlWithFilterApplied()
    {
        yield ['nova/resources/users?users_page=1&users_filter=W3siQXBwXFxOb3ZhXFxGaWx0ZXJzXFxTZWxlY3RGaXJzdCI6IjMifSx7IkFwcFxcTm92YVxcRmlsdGVyc1xcQ3JlYXRlZCI6IiJ9XQ'];
        yield ['nova/resources/users?users_page=1&users_filter=W3siY2xhc3MiOiJBcHBcXE5vdmFcXEZpbHRlcnNcXFNlbGVjdEZpcnN0IiwidmFsdWUiOiIzIn0seyJjbGFzcyI6IkFwcFxcTm92YVxcRmlsdGVyc1xcQ3JlYXRlZCIsInZhbHVlIjoiIn1d'];
    }
}
