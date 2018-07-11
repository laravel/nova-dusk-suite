<?php

namespace Tests\Browser;

use App\Post;
use App\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\IndexComponent;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class DetailTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function can_view_resource_attributes()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->assertSee('Taylor Otwell')
                    ->assertSee('taylor@laravel.com');
        });
    }

    /**
     * @test
     */
    public function can_run_actions_on_resource()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->runAction('mark-as-active');

            $this->assertEquals(1, User::find(1)->active);
        });
    }

    /**
     * @test
     */
    public function actions_can_be_cancelled_without_effect()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->cancelAction('mark-as-active');

            $this->assertEquals(0, User::find(1)->active);
        });
    }

    /**
     * @test
     */
    public function can_navigate_to_edit_page()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->click('@edit-resource-button')
                    ->pause(250)
                    ->assertPathIs('/nova/resources/users/1/edit');
        });
    }

    /**
     * @test
     */
    public function resource_can_be_deleted()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 3))
                    ->delete();

            $this->assertNull(User::where('id', 3)->first());
        });
    }

    /**
     * @test
     */
    public function relationships_can_be_searched()
    {
        $this->seed();

        $user = User::find(1);
        $user->posts()->save($post = factory(Post::class)->create());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->within(new IndexComponent('posts'), function ($browser) {
                        $browser->assertSeeResource(1)
                                ->searchFor('No Matching Posts')
                                ->assertDontSeeResource(1);
                    });
        });
    }

    /**
     * @test
     */
    public function can_navigate_to_create_relationship_screen()
    {
        $this->seed();

        $user = User::find(1);
        $user->posts()->save($post = factory(Post::class)->create());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->within(new IndexComponent('posts'), function ($browser) {
                        $browser->click('@create-button')
                                ->assertPathIs('/nova/resources/posts/new')
                                ->assertQueryStringHas('viaResource', 'users')
                                ->assertQueryStringHas('viaResourceId', '1')
                                ->assertQueryStringHas('viaRelationship', 'posts');
                    });
        });
    }

    /**
     * @test
     */
    public function relations_can_be_sorted()
    {
        $this->seed();

        $user = User::find(1);
        $user->posts()->saveMany(factory(Post::class, 10)->create());

        $user2 = User::find(2);
        $user2->posts()->save(factory(Post::class)->create());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
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
        });
    }

    /**
     * @test
     */
    public function actions_on_all_matching_relations_should_be_scoped_to_the_relation()
    {
        $this->seed();

        $user = User::find(1);
        $user->posts()->save($post = factory(Post::class)->create());

        $user2 = User::find(2);
        $user2->posts()->save($post2 = factory(Post::class)->create());

        $this->browse(function (Browser $browser) use ($post, $post2) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->within(new IndexComponent('posts'), function ($browser) {
                        $browser->selectAllMatching()
                                ->runAction('mark-as-active');
                    });

            $this->assertEquals(1, $post->fresh()->active);
            $this->assertEquals(0, $post2->fresh()->active);
        });
    }

    /**
     * @test
     */
    public function deleting_all_matching_relations_is_scoped_to_the_relationships()
    {
        $this->seed();

        $user = User::find(1);
        $user->posts()->save($post = factory(Post::class)->create());

        $user2 = User::find(2);
        $user2->posts()->save($post2 = factory(Post::class)->create());

        $this->browse(function (Browser $browser) use ($post, $post2) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->within(new IndexComponent('posts'), function ($browser) {
                        $browser->selectAllMatching()
                                ->deleteSelected();
                    });

            $this->assertNull($post->fresh());
            $this->assertNotNull($post2->fresh());
        });
    }
}
