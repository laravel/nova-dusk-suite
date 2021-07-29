<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\PostFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\Page;
use Laravel\Nova\Tests\DuskTestCase;

class DetailAuthorizationTest extends DuskTestCase
{
    /**
     * @test
     */
    public function detail_page_should_not_be_accessible_if_not_authorized_to_view()
    {
        $user = User::find(1);
        $post = PostFactory::new()->create();
        $user->shouldBlockFrom('post.view.'.$post->id);

        $this->browse(function (Browser $browser) use ($user, $post) {
            $browser->loginAs($user)
                    ->visit(new Page("/resources/posts/{$post->id}"))
                    ->assertForbidden();

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function cant_navigate_to_edit_page_if_not_authorized()
    {
        $user = User::find(1);
        $post = PostFactory::new()->create();
        $user->shouldBlockFrom('post.update.'.$post->id);

        $this->browse(function (Browser $browser) use ($user, $post) {
            $browser->loginAs($user)
                    ->visit(new Detail('posts', $post->id))
                    ->assertMissing('@edit-resource-button');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function resource_cant_be_deleted_if_not_authorized()
    {
        $user = User::find(1);
        $post = PostFactory::new()->create();
        $user->shouldBlockFrom('post.delete.'.$post->id);

        $this->browse(function (Browser $browser) use ($user, $post) {
            $browser->loginAs($user)
                    ->visit(new Detail('posts', $post->id))
                    ->assertMissing('@open-delete-modal-button');

            $browser->blank();
        });
    }
}
