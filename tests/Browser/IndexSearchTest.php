<?php

namespace Laravel\Nova\Tests\Browser;

use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Components\SidebarComponent;
use Laravel\Nova\Testing\Browser\Pages\UserIndex;
use Laravel\Nova\Tests\DuskTestCase;

class IndexSearchTest extends DuskTestCase
{
    public function test_resources_can_be_searched()
    {
        $this->browse(function (Browser $browser) {
            // Search For Single User By ID...
            $browser->loginAs(1)
                ->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->searchFor('3')
                        ->waitForTable()
                        ->assertDontSeeResource(1)
                        ->assertDontSeeResource(2)
                        ->assertSeeResource(3)
                        ->assertSee('1-1 of 1');
                });

            // Search For Single User By Name...
            $browser->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->searchFor('Taylor')
                        ->waitForTable()
                        ->assertSeeResource(1)
                        ->assertDontSeeResource(2)
                        ->assertDontSeeResource(3);
                });

            $browser->blank();
        });
    }

    public function test_resources_search_query_will_reset_on_revisit()
    {
        $this->browse(function (Browser $browser) {
            // Search For Single User By ID...
            $browser->loginAs(1)
                ->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->searchFor('3')
                        ->waitForTable()
                        ->assertDontSeeResource(1)
                        ->assertDontSeeResource(2)
                        ->assertSeeResource(3)
                        ->assertDontSeeResource(4)
                        ->assertQueryStringHas('users_search', '3');
                })
                ->within(new SidebarComponent(), function ($browser) {
                    $browser->clickLink('Users');
                })
                ->waitForTextIn('h1', 'Users')
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->assertValue('@search', '')
                        ->assertQueryStringMissing('users_search', '')
                        ->assertSeeResource(1)
                        ->assertSeeResource(2)
                        ->assertSeeResource(3)
                        ->assertSeeResource(4);
                });

            $browser->blank();
        });
    }

    public function test_resources_search_query_can_be_bookmarked()
    {
        $this->browse(function (Browser $browser) {
            // Search For Single User By ID...
            $browser->loginAs(1)
                ->visit(new UserIndex(['users_page' => 1, 'users_search' => 'taylor']))
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->assertSeeResource(1)
                        ->assertDontSeeResource(2)
                        ->assertDontSeeResource(3)
                        ->assertDontSeeResource(4)
                        ->assertQueryStringHas('users_search', 'taylor');
                });

            $browser->blank();
        });
    }
}
