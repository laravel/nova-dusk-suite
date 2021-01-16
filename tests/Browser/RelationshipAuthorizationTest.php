<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\PostFactory;
use Database\Factories\TagFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Attach;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class RelationshipAuthorizationTest extends DuskTestCase
{
    /**
     * @test
     */
    public function resource_cant_be_added_to_parent_if_not_authorized()
    {
        $user = User::find(1);
        $user->shouldBlockFrom('user.addPost.'.$user->id);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs(User::find(1))
                    ->visit(new Create('posts'))
                    ->pause(500)
                    ->assertSelectMissingOption('@user', $user->id)
                    ->assertSelectMissingOption('@user', $user->name);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function morphable_resource_cant_be_added_to_parent_if_not_authorized()
    {
        $this->markTestSkipped('Unable to use `assertSelectMissingOption` with search selection');

        $user = User::find(1);
        $post = PostFactory::new()->create();
        $user->shouldBlockFrom('post.addComment.'.$post->id);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(User::find(1))
                    ->visit(new Create('comments'))
                    ->select('@commentable-type', 'posts')
                    ->pause(500)
                    ->assertSelectMissingOption('@commentable-select', $post->title)
                    ->assertSelectMissingOption('@commentable-select', $post->id);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function create_button_should_be_missing_from_detail_index_when_not_authorized()
    {
        $user = User::find(1);
        $post = PostFactory::new()->create();
        $user->shouldBlockFrom('post.addComment.'.$post->id);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('posts', 1))
                    ->within(new IndexComponent('comments'), function ($browser) {
                        $browser->assertMissing('@create-button');
                    });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function resource_cant_be_attached_to_parent_if_not_authorized()
    {
        $this->markTestSkipped('Unable to use `assertSelectMissingOption` with search selection');

        $user = User::find(1);
        $post = PostFactory::new()->create();
        $tag = TagFactory::new()->create();
        $user->shouldBlockFrom('post.attachTag.'.$post->id);

        $this->browse(function (Browser $browser) use ($tag) {
            $browser->loginAs(User::find(1))
                    ->visit(new Attach('posts', 1, 'tags'))
                    ->assertSelectMissingOption('@attachable-select', $tag->name)
                    ->assertSelectMissingOption('@attachable-select', $tag->id);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function attach_button_should_be_missing_from_detail_index_when_not_authorized()
    {
        $user = User::find(1);
        $post = PostFactory::new()->create();
        $user->shouldBlockFrom('post.attachAnyTag.'.$post->id);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('posts', 1))
                    ->within(new IndexComponent('tags'), function ($browser) {
                        $browser->assertMissing('@attach-button');
                    });

            $browser->blank();
        });
    }
}
