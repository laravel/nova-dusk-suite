<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\PostFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class DetailTest extends DuskTestCase
{
    /**
     * @test
     */
    public function can_view_resource_attributes()
    {
        $this->setupLaravel();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->assertSee('User Details: 1')
                    ->assertSee('Taylor Otwell')
                    ->assertSee('taylor@laravel.com');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_run_actions_on_resource()
    {
        $this->setupLaravel();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->runAction('mark-as-active')
                    ->pause(3000);

            $this->assertEquals(1, User::find(1)->active);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function actions_can_be_cancelled_without_effect()
    {
        $this->setupLaravel();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->cancelAction('mark-as-active');

            $this->assertEquals(0, User::find(1)->active);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_navigate_to_edit_page()
    {
        $this->setupLaravel();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->click('@edit-resource-button')
                    ->pause(250)
                    ->assertPathIs('/nova/resources/users/1/edit');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function resource_can_be_deleted()
    {
        $this->setupLaravel();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 3))
                    ->delete()
                    ->waitForText('The user was deleted', 10)
                    ->assertPathIs('/nova/resources/users');

            $this->assertNull(User::where('id', 3)->first());

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function relationships_can_be_searched()
    {
        $this->setupLaravel();

        $user = User::find(1);
        $user->posts()->save($post = PostFactory::new()->create());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->waitFor('@posts-index-component', 10)
                    ->within(new IndexComponent('posts'), function ($browser) {
                        $browser->assertSeeResource(1)
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
        $this->setupLaravel();

        $user = User::find(1);
        $user->posts()->save($post = PostFactory::new()->create());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->waitFor('@posts-index-component', 10)
                    ->within(new IndexComponent('posts'), function ($browser) {
                        $browser->click('@create-button')
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
        $this->setupLaravel();

        $user = User::find(1);
        $user->posts()->saveMany(PostFactory::new()->times(10)->create());

        $user2 = User::find(2);
        $user2->posts()->save(PostFactory::new()->create());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->waitFor('@posts-index-component', 10)
                    ->within(new IndexComponent('posts'), function ($browser) {
                        $browser->assertSeeResource(10)
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
        $this->setupLaravel();

        $user = User::find(1);
        $user->posts()->saveMany(PostFactory::new()->times(10)->create());

        $user2 = User::find(2);
        $user2->posts()->save(PostFactory::new()->create());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->waitFor('@posts-index-component', 10)
                    ->within(new IndexComponent('posts'), function ($browser) {
                        $browser->assertSeeResource(10)
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
    public function actions_on_all_matching_relations_should_be_scoped_to_the_relation()
    {
        $this->setupLaravel();

        $user = User::find(1);
        $user->posts()->save($post = PostFactory::new()->create());

        $user2 = User::find(2);
        $user2->posts()->save($post2 = PostFactory::new()->create());

        $this->browse(function (Browser $browser) use ($post, $post2) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->waitFor('@posts-index-component', 10)
                    ->within(new IndexComponent('posts'), function ($browser) {
                        $browser->selectAllMatching()
                                ->runAction('mark-as-active');
                    });

            $this->assertEquals(1, $post->fresh()->active);
            $this->assertEquals(0, $post2->fresh()->active);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function deleting_all_matching_relations_is_scoped_to_the_relationships()
    {
        $this->setupLaravel();

        $user = User::find(1);
        $user->posts()->save($post = PostFactory::new()->create());

        $user2 = User::find(2);
        $user2->posts()->save($post2 = PostFactory::new()->create());

        $this->browse(function (Browser $browser) use ($post, $post2) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->waitFor('@posts-index-component', 10)
                    ->within(new IndexComponent('posts'), function ($browser) {
                        $browser->selectAllMatching()
                                ->deleteSelected();
                    });

            $this->assertNull($post->fresh());
            $this->assertNotNull($post2->fresh());

            $browser->blank();
        });
    }
}
