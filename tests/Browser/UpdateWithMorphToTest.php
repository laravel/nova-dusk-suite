<?php

namespace Tests\Browser;

use App\Post;
use App\User;
use App\Comment;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UpdateWithMorphToTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function resource_can_be_updated_to_new_parent()
    {
        $this->seed();

        $comment = factory(Comment::class)->create();
        $post = factory(Post::class)->create();

        $this->browse(function (Browser $browser) use ($comment) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Update('comments', $comment->id))
                    ->select('@commentable-select', 2)
                    ->update();

            $this->assertCount(0, Post::find(1)->comments);
            $this->assertCount(1, Post::find(2)->comments);
        });
    }
}
