<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\PostFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Page;
use Laravel\Nova\Tests\DuskTestCase;

class UpdateAuthorizationTest extends DuskTestCase
{
    /**
     * @test
     */
    public function update_page_should_not_be_accessible_if_not_authorized_to_view()
    {
        $post = PostFactory::new()->create();
        User::find(1)->shouldBlockFrom('post.update.'.$post->id);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(1)
                ->visit(new Page("/resources/posts/{$post->id}/edit"))
                ->assertForbidden();

            $browser->blank();
        });
    }
}
