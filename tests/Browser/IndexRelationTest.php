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
        $user->posts()->save($post = PostFactory::new()->create());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new Detail('users', 1))
                    ->waitForTextIn('h1', 'User Details: 1')
                    ->within(new IndexComponent('posts'), function ($browser) {
                        $browser->waitForTable()
                                ->assertSeeResource(1)
                                ->searchFor('No Matching Posts')
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
        $user = User::find(1);
        $user->posts()->save($post = PostFactory::new()->create());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new Detail('users', 1))
                    ->waitForTextIn('h1', 'User Details: 1')
                    ->within(new IndexComponent('posts'), function ($browser) {
                        $browser->waitFor('@create-button')
                                ->click('@create-button')
                                ->assertPathIs('/nova/resources/posts/new')
                                ->assertQueryStringHas('viaResource', 'users')
                                ->assertQueryStringHas('viaResourceId', '1')
                                ->assertQueryStringHas('viaRelationship', 'posts');
                    });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function relations_can_be_paginated()
    {
        $user = User::find(1);
        $user->posts()->saveMany(PostFactory::new()->times(10)->create());

        $user2 = User::find(2);
        $user2->posts()->save(PostFactory::new()->create());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new Detail('users', 1))
                    ->waitForTextIn('h1', 'User Details: 1')
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
        $user = User::find(1);
        $user->posts()->saveMany(PostFactory::new()->times(10)->create());

        $user2 = User::find(2);
        $user2->posts()->save(PostFactory::new()->create());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new Detail('users', 1))
                    ->waitForTextIn('h1', 'User Details: 1')
                    ->within(new IndexComponent('posts'), function ($browser) {
                        $browser->waitForTable(25)
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
        $user = User::find(1);
        $user->posts()->save($post = PostFactory::new()->create());

        $user2 = User::find(2);
        $user2->posts()->save($post2 = PostFactory::new()->create());

        $this->browse(function (Browser $browser) use ($post, $post2) {
            $browser->loginAs(1)
                    ->visit(new Detail('users', 1))
                    ->waitForTextIn('h1', 'User Details: 1')
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
}
