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
    public function test_detail_page_should_not_be_accessible_if_not_authorized_to_view()
    {
        $post = PostFactory::new()->create();
        User::find(1)->shouldBlockFrom('post.view.'.$post->id);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(1)
                ->visit(new Page("/resources/posts/{$post->id}"))
                ->assertForbidden();

            $browser->blank();
        });
    }

    public function test_cant_navigate_to_edit_page_if_not_authorized()
    {
        $post = PostFactory::new()->create();
        User::find(1)->shouldBlockFrom('post.update.'.$post->id);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(1)
                ->visit(new Detail('posts', $post->id))
                ->assertMissing('@edit-resource-link');

            $browser->blank();
        });
    }

    public function test_resource_cant_be_deleted_if_not_authorized()
    {
        $post = PostFactory::new()->create();
        User::find(1)->shouldBlockFrom('post.delete.'.$post->id);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(1)
                ->visit(new Detail('posts', $post->id))
                ->assertMissing('@open-delete-modal-button');

            $browser->blank();
        });
    }
}
