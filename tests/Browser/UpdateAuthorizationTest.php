<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\PostFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Nova;
use Laravel\Nova\Testing\Browser\Pages\Forbidden;
use Laravel\Nova\Tests\DuskTestCase;

class UpdateAuthorizationTest extends DuskTestCase
{
    /**
     * @test
     */
    public function update_page_should_not_be_accessible_if_not_authorized_to_view()
    {
        $user = User::find(1);
        $post = PostFactory::new()->create();
        $user->shouldBlockFrom('post.update.'.$post->id);

        $this->browse(function (Browser $browser) use ($user, $post) {
            $browser->loginAs($user)
                    ->visit(Nova::path()."/resources/posts/{$post->id}/edit")
                    ->on(new Forbidden);

            $browser->blank();
        });
    }
}
