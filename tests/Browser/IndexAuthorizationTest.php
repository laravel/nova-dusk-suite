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
}
