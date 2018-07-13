<?php

namespace Tests\Browser;

use App\Post;
use App\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\IndexComponent;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class IndexAuthorizationTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function resource_index_can_be_totally_blocked_via_view_any()
    {
        $this->seed();

        $post = factory(Post::class)->create();

        $user = User::find(1);
        $user->shouldBlockFrom('post.viewAny');

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Index('posts'))
                    ->assertPathIs('/nova/403');
        });
    }

    /**
     * @test
     */
    public function shouldnt_see_edit_button_if_blocked_from_updating()
    {
        $this->seed();

        $post = factory(Post::class)->create();
        $post2 = factory(Post::class)->create();

        $user = User::find(1);
        $user->shouldBlockFrom('post.update.'.$post->id);

        $this->browse(function (Browser $browser) use ($post, $post2) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Index('posts'))
                    ->within(new IndexComponent('posts'), function ($browser) use ($post, $post2) {
                        $browser->assertMissing('@'.$post->id.'-edit-button');
                        $browser->assertVisible('@'.$post2->id.'-edit-button');
                    });
        });
    }

    /**
     * @test
     */
    public function shouldnt_see_delete_button_if_blocked_from_deleting()
    {
        $this->seed();

        $post = factory(Post::class)->create();
        $post2 = factory(Post::class)->create();

        $user = User::find(1);
        $user->shouldBlockFrom('post.delete.'.$post->id);

        $this->browse(function (Browser $browser) use ($post, $post2) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Index('posts'))
                    ->within(new IndexComponent('posts'), function ($browser) use ($post, $post2) {
                        $browser->assertMissing('@'.$post->id.'-delete-button');
                        $browser->assertVisible('@'.$post2->id.'-delete-button');
                    });
        });
    }
}
