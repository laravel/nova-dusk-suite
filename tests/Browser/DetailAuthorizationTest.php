<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\PostFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class DetailAuthorizationTest extends DuskTestCase
{
    /**
     * @test
     */
    public function detail_page_should_not_be_accessible_if_not_authorized_to_view()
    {
        $this->setupLaravel();

        $user = User::find(1);
        $post = PostFactory::new()->create();
        $user->shouldBlockFrom('post.view.'.$post->id);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('posts', $post->id))
                    ->assertPathIs('/nova/403');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function cant_navigate_to_edit_page_if_not_authorized()
    {
        $this->setupLaravel();

        $user = User::find(1);
        $post = PostFactory::new()->create();
        $user->shouldBlockFrom('post.update.'.$post->id);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(User::find(1))
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
        $this->setupLaravel();

        $user = User::find(1);
        $post = PostFactory::new()->create();
        $user->shouldBlockFrom('post.delete.'.$post->id);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('posts', $post->id))
                    ->assertMissing('@open-delete-modal-button');

            $browser->blank();
        });
    }
}
