<?php

namespace Tests\Browser;

use App\Tag;
use App\Post;
use App\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

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
}
