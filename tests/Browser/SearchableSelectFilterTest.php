<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\PostFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Components\SearchInputComponent;
use Laravel\Nova\Testing\Browser\Pages\Index;
use Laravel\Nova\Tests\DuskTestCase;

class SearchableSelectFilterTest extends DuskTestCase
{
    public function test_it_can_search_select_of_searchable_filter()
    {
        PostFactory::times(3)
            ->sequence(
                ['user_id' => 1],
                ['user_id' => 2],
                ['user_id' => 3],
            )
            ->create();

        $this->defineApplicationStates('searchable');

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Index('posts'))
                ->within(new IndexComponent('posts'), function ($browser) {
                    $browser
                        ->waitForTable()
                        ->assertSeeResource(1)
                        ->assertSeeResource(2)
                        ->assertSeeResource(3)
                        ->runFilter(function ($browser) {
                            $browser->within(new SearchInputComponent('select-first-select-filter'), function ($browser) {
                                $browser->searchAndSelectFirstResult('Second')->pause(1000);
                            });
                        })
                        ->assertDontSeeResource(1)
                        ->assertSeeResource(2)
                        ->assertDontSeeResource(3);
                });

            $browser->blank();
        });
    }
}
