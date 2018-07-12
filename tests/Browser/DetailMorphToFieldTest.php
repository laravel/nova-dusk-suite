<?php

namespace Tests\Browser;

use App\Post;
use App\User;
use App\Comment;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\DetailComponent;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class DetailMorphToFieldTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function morph_to_field_navigates_to_parent_resource_when_clicked()
    {
        $this->seed();

        $post = factory(Post::class)->create();
        $post->comments()->save($comment = factory(Comment::class)->make());

        $this->browse(function (Browser $browser) use ($post, $comment) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('comments', $comment->id))
                    ->within(new DetailComponent('comments', $comment->id), function ($browser) {
                        $browser->assertSee('Post');
                    })
                    ->clickLink($post->title)
                    ->pause(250)
                    ->assertPathIs('/nova/resources/posts/'.$post->id);
        });
    }
}
