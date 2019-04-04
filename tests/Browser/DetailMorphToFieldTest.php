<?php

namespace Tests\Browser;

use App\Link;
use App\Post;
use App\User;
use App\Video;
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
                    ->pause(500)
                    ->assertPathIs('/nova/resources/posts/'.$post->id);
        });
    }

    /**
     * @test
     */
    public function morph_to_field_should_honor_custom_labels()
    {
        $this->seed();

        $post = factory(Post::class)->create();
        $post->comments()->save($comment = factory(Comment::class)->make());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('comments', 1))
                    ->assertSee('User Post');
        });
    }

    /**
     * @test
     */
    public function morph_to_field_should_honor_custom_labels_again()
    {
        $this->seed();

        $video = factory(Video::class)->create();
        $video->comments()->save($comment = factory(Comment::class)->make());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('comments', 1))
                    ->assertSee('User Video');
        });
    }

    /**
     * @test
     */
    public function morph_to_field_should_honor_custom_polymorphic_type()
    {
        $this->seed();

        $link = factory(Link::class)->create();
        $link->comments()->save($comment = factory(Comment::class)->make());

        $this->browse(function (Browser $browser) use ($comment, $link) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('comments', 1))
                    ->within(new DetailComponent('comments', $comment->id), function ($browser) use ($link) {
                        $browser->assertSee('Link')
                                ->assertSee($link->title);
                    });
        });
    }
}
