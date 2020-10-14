<?php

namespace Laravel\Nova\Tests\Browser;

use App\Post;
use App\User;
use Laravel\Dusk\Browser;
use Laravel\Nova\Tests\DuskTestCase;

class UpdateAuthorizationTest extends DuskTestCase
{
    /**
     * @test
     */
    public function update_page_should_not_be_accessible_if_not_authorized_to_view()
    {
        $this->setupLaravel();

        $user = User::find(1);
        $post = factory(Post::class)->create();
        $user->shouldBlockFrom('post.update.'.$post->id);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Update('posts', $post->id))
                    ->assertPathIs('/nova/403');

            $browser->blank();
        });
    }
}
