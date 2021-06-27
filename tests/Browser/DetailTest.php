<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\PostFactory;
use Database\Factories\UserFactory;
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
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->waitForTextIn('h1', 'User Details: 1')
                    ->assertSee('User Details: 1')
                    ->assertSee('Taylor Otwell')
                    ->assertSee('taylor@laravel.com');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_view_resource_as_big_int()
    {
        $user = UserFactory::new()->create([
            'id' => 9121018173229432287,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', $user->id))
                    ->waitForTextIn('h1', 'User Details: '.$user->id)
                    ->assertSee('User Details: '.$user->id)
                    ->assertSee($user->email);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_navigate_to_edit_page()
    {
        $this->markTestIncomplete('Missing edit button');

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->edit()
                    ->waitForTextIn('h1', 'Update User')
                    ->assertPathIs('/nova/resources/users/1/edit');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_navigate_to_edit_page_using_shortcut()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->keys('', ['e'])
                    ->waitForTextIn('h1', 'Update User')
                    ->assertPathIs('/nova/resources/users/1/edit');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_navigate_to_replicate_resource_screen()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 2))
                    ->replicate()
                    ->waitForTextIn('h1', 'Create User')
                    ->assertPathIs('/nova/resources/users/2/replicate')
                    ->assertInputValue('@name', 'Mohamed Said')
                    ->assertInputValue('@email', 'mohamed@laravel.com')
                    ->assertSee('Create & Add Another')
                    ->assertSee('Create User');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function cannot_navigate_to_replicate_resource_screen_when_blocked_via_policy()
    {
        $this->markTestIncomplete('Missing edit button');

        $user = User::find(1);
        $user->shouldBlockFrom('user.replicate.4');

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit(new Detail('users', 4))
                    ->waitFor('@edit-resource-button')
                    ->assertNotPresent('@replicate-resource-button');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_navigate_to_different_detail_screen()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 2))
                    ->waitForTextIn('h1', 'User Details: 2')
                    ->assertSeeIn('@users-detail-component', 'Mohamed Said');

            $browser->script([
                'Nova.app.$router.push({ name: "detail", params: { resourceName: "users", resourceId: 3 }});',
            ]);

            $browser->waitForTextIn('h1', 'User Details: 3')
                    ->assertPathIs('/nova/resources/users/3')
                    ->assertSeeIn('@users-detail-component', 'David Hemphill');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function resource_can_be_deleted()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 3))
                    ->waitForTextIn('h1', 'User Details: 3')
                    ->delete()
                    ->waitForText('The user was deleted')
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
        $user = User::find(1);
        $user->posts()->save($post = PostFactory::new()->create());

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
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

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
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
        PostFactory::new()->times(10)->create([
            'user_id' => 1,
        ]);

        PostFactory::new()->create([
            'user_id' => 2,
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
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
        PostFactory::new()->times(10)->create([
            'user_id' => 1,
        ]);

        PostFactory::new()->create([
            'user_id' => 2,
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
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
        $post = PostFactory::new()->create([
            'user_id' => 1,
        ]);

        $post2 = PostFactory::new()->create([
            'user_id' => 2,
        ]);

        $this->browse(function (Browser $browser) use ($post, $post2) {
            $browser->loginAs(User::find(1))
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
