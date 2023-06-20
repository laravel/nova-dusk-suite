<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\LensComponent;
use Laravel\Nova\Testing\Browser\Pages\Lens;
use Laravel\Nova\Tests\DuskTestCase;

class LensSearchTest extends DuskTestCase
{
    public function test_lens_can_be_searched()
    {
        $this->browse(function (Browser $browser) {
            PostFactory::new()->times(6)
                ->state(new Sequence(
                    ['user_id' => 1],
                    ['user_id' => 2],
                    ['user_id' => 3],
                    ['user_id' => 4],
                ))
                ->create();

            // Search For Single Post By ID...
            $browser->loginAs(1)
                ->visit(new Lens('posts', 'post'))
                ->within(new LensComponent('posts', 'post'), function ($browser) {
                    $browser->waitForTable()
                        ->searchFor('3')
                        ->waitForTable()
                        ->assertDontSeeResource(1)
                        ->assertDontSeeResource(2)
                        ->assertSeeResource(3)
                        ->assertSee('1-1 of 1');
                });

            // Search For Single Post By Name...
            $browser->visit(new Lens('posts', 'post'))
                ->within(new LensComponent('posts', 'post'), function ($browser) {
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

    public function test_lens_search_query_can_be_bookmarked()
    {
        $this->browse(function (Browser $browser) {
            PostFactory::new()->times(6)
                ->state(new Sequence(
                    ['user_id' => 1],
                    ['user_id' => 2],
                    ['user_id' => 3],
                    ['user_id' => 4],
                ))
                ->create();

            $browser->loginAs(1)
                ->visit(new Lens('posts', 'post', ['posts_page' => 1, 'posts_search' => 'taylor']))
                ->within(new LensComponent('posts', 'post'), function ($browser) {
                    $browser->waitForTable()
                        ->assertSeeResource(1)
                        ->assertDontSeeResource(2)
                        ->assertDontSeeResource(3)
                        ->assertDontSeeResource(4)
                        ->assertSeeResource(5)
                        ->assertQueryStringHas('posts_search', 'taylor');
                });

            $browser->blank();
        });
    }
}
