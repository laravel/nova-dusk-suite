<?php

namespace Tests\Browser;

use App\Post;
use App\Tag;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\IndexComponent;
use Tests\DuskTestCase;

class RelationshipAuthorizationTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function resource_cant_be_added_to_parent_if_not_authorized()
    {
        $this->seed();

        $user = User::find(1);
        $user->shouldBlockFrom('user.addPost.'.$user->id);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Create('posts'))
                    ->assertSelectMissingOption('@user', $user->id)
                    ->assertSelectMissingOption('@user', $user->name);
        });
    }

    /**
     * @test
     */
    public function morphable_resource_cant_be_added_to_parent_if_not_authorized()
    {
        $this->seed();

        $user = User::find(1);
        $post = factory(Post::class)->create();
        $user->shouldBlockFrom('post.addComment.'.$post->id);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Create('comments'))
                    ->select('@commentable-type', 'posts')
                    ->pause(500)
                    ->assertSelectMissingOption('@commentable-select', $post->title)
                    ->assertSelectMissingOption('@commentable-select', $post->id);
        });
    }

    /**
     * @test
     */
    public function create_button_should_be_missing_from_detail_index_when_not_authorized()
    {
        $this->seed();

        $user = User::find(1);
        $post = factory(Post::class)->create();
        $user->shouldBlockFrom('post.addComment.'.$post->id);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('posts', 1))
                    ->within(new IndexComponent('comments'), function ($browser) {
                        $browser->assertMissing('@create-button');
                    });
        });
    }

    /**
     * @test
     */
    public function resource_cant_be_attached_to_parent_if_not_authorized()
    {
        $this->seed();

        $user = User::find(1);
        $post = factory(Post::class)->create();
        $tag = factory(Tag::class)->create();
        $user->shouldBlockFrom('post.attachTag.'.$post->id);

        $this->browse(function (Browser $browser) use ($post, $tag) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Attach('posts', 1, 'tags'))
                    ->assertSelectMissingOption('@attachable-select', $tag->name)
                    ->assertSelectMissingOption('@attachable-select', $tag->id);
        });
    }

    /**
     * @test
     */
    public function attach_button_should_be_missing_from_detail_index_when_not_authorized()
    {
        $this->seed();

        $user = User::find(1);
        $post = factory(Post::class)->create();
        $user->shouldBlockFrom('post.attachAnyTag.'.$post->id);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('posts', 1))
                    ->within(new IndexComponent('tags'), function ($browser) {
                        $browser->assertMissing('@attach-button');
                    });
        });
    }
}
