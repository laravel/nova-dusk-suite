<?php

namespace Tests\Browser;

use App\Post;
use App\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class DetailAuthorizationTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function detail_page_should_not_be_accessible_if_not_authorized_to_view()
    {
        $this->seed();

        $user = User::find(1);
        $post = factory(Post::class)->create();
        $user->shouldBlockFrom('post.view.'.$post->id);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('posts', $post->id))
                    ->assertPathIs('/nova/403');
        });
    }

    /**
     * @test
     */
    public function cant_navigate_to_edit_page_if_not_authorized()
    {
        $this->seed();

        $user = User::find(1);
        $post = factory(Post::class)->create();
        $user->shouldBlockFrom('post.update.'.$post->id);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('posts', $post->id))
                    ->assertMissing('@edit-resource-button');
        });
    }

    /**
     * @test
     */
    public function resource_cant_be_deleted_if_not_authorized()
    {
        $this->seed();

        $user = User::find(1);
        $post = factory(Post::class)->create();
        $user->shouldBlockFrom('post.delete.'.$post->id);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('posts', $post->id))
                    ->assertMissing('@open-delete-modal-button');
        });
    }
}
