<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\PostFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class IndexRelationTest extends DuskTestCase
{
    /**
     * @test
     */
    public function relationships_can_be_searched()
    {
        $user = User::find(1);
        $user->posts()->save(PostFactory::new()->make());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->waitForTextIn('h1', 'User Details: Taylor Otwell')
                ->within(new IndexComponent('posts'), function ($browser) {
                    $browser->waitForTable()
                        ->assertSeeResource(1)
                        ->searchFor('No Matching Posts')
                        ->waitForEmptyDialog()
                        ->assertDontSeeResource(1);
                });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_navigate_to_create_relationship_screen()
    {
        PostFactory::new()->create([
            'user_id' => 1,
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->waitForTextIn('h1', 'User Details: Taylor Otwell')
                ->runCreateRelation('posts')
                ->assertQueryStringHas('viaResource', 'users')
                ->assertQueryStringHas('viaResourceId', '1')
                ->assertQueryStringHas('viaRelationship', 'posts');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function relations_can_be_paginated()
    {
        PostFactory::new()->times(10)->create(['user_id' => 1]);
        PostFactory::new()->create(['user_id' => 2]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->waitForTextIn('h1', 'User Details: Taylor Otwell')
                ->within(new IndexComponent('posts'), function ($browser) {
                    $browser->waitForTable()
                        ->assertSeeResource(10)
                        ->assertDontSeeResource(1)
                        ->nextPage()
                        ->assertDontSeeResource(10)
                        ->assertSeeResource(1)
                        ->previousPage()
                        ->assertSeeResource(10)
                        ->assertDontSeeResource(1);
                });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function relations_can_be_sorted()
    {
        PostFactory::new()->times(10)->create(['user_id' => 1]);
        PostFactory::new()->create(['user_id' => 2]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->waitForTextIn('h1', 'User Details: Taylor Otwell')
                ->within(new IndexComponent('posts'), function ($browser) {
                    $browser->waitForTable()
                        ->assertSeeResource(10)
                        ->assertSeeResource(6)
                        ->assertDontSeeResource(1)
                        ->sortBy('id')
                        ->assertDontSeeResource(10)
                        ->assertDontSeeResource(6)
                        ->assertSeeResource(5)
                        ->assertSeeResource(1);
                });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function deleting_all_matching_relations_is_scoped_to_the_relationships()
    {
        $post = PostFactory::new()->create(['user_id' => 1]);
        $post2 = PostFactory::new()->create(['user_id' => 2]);

        $this->browse(function (Browser $browser) use ($post, $post2) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->waitForTextIn('h1', 'User Details: Taylor Otwell')
                ->within(new IndexComponent('posts'), function ($browser) {
                    $browser->waitForTable()
                        ->selectAllMatching()
                        ->deleteSelected();
                });

            $this->assertNull($post->fresh());
            $this->assertNotNull($post2->fresh());

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function relations_filter_should_not_change_query_string_when_filter_has_not_been_applied()
    {
        PostFactory::new()->times(10)->create(['user_id' => 1]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->waitForTextIn('h1', 'User Details: Taylor Otwell')
                ->within(new IndexComponent('posts'), function ($browser) {
                    $browser->waitForTable()
                        ->assertQueryStringMissing('posts_filter')
                        ->selectFilter('Select First', '3')
                        ->waitForEmptyDialog()
                        ->assertQueryStringHas('posts_filter', 'W3siQXBwXFxOb3ZhXFxGaWx0ZXJzXFxTZWxlY3RGaXJzdCI6IjMifSx7IkFwcFxcTm92YVxcRmlsdGVyc1xcVXNlclBvc3QiOnsiaGFzLWF0dGFjaG1lbnQiOmZhbHNlfX1d');
                });

            $browser->blank();
        });
    }
}
