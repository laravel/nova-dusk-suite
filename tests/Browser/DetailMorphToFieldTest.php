<?php

namespace Laravel\Nova\Tests\Browser;

use App\Comment;
use App\Link;
use App\Post;
use App\User;
use App\Video;
use Laravel\Dusk\Browser;
use Laravel\Nova\Tests\Browser\Components\DetailComponent;
use Laravel\Nova\Tests\DuskTestCase;

class DetailMorphToFieldTest extends DuskTestCase
{
    /**
     * @test
     */
    public function morph_to_field_navigates_to_parent_resource_when_clicked()
    {
        $this->setupLaravel();

        $post = factory(Post::class)->create();
        $post->comments()->save($comment = factory(Comment::class)->make());

        $this->browse(function (Browser $browser) use ($post, $comment) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('comments', $comment->id))
                    ->within(new DetailComponent('comments', $comment->id), function ($browser) {
                        $browser->assertSee('Post');
                    })
                    ->clickLink($post->title)
                    ->waitForText('User Post Details: '.$post->id)
                    ->assertPathIs('/nova/resources/posts/'.$post->id);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function morph_to_field_should_honor_custom_labels()
    {
        $this->setupLaravel();

        $post = factory(Post::class)->create();
        $post->comments()->save($comment = factory(Comment::class)->make());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('comments', 1))
                    ->assertSee('User Post');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function morph_to_field_should_honor_custom_labels_again()
    {
        $this->setupLaravel();

        $video = factory(Video::class)->create();
        $video->comments()->save($comment = factory(Comment::class)->make());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('comments', 1))
                    ->assertSee('User Video');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function morph_to_field_should_honor_custom_polymorphic_type()
    {
        $this->setupLaravel();

        $link = factory(Link::class)->create();
        $link->comments()->save($comment = factory(Comment::class)->make());

        $this->browse(function (Browser $browser) use ($comment, $link) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('comments', 1))
                    ->within(new DetailComponent('comments', $comment->id), function ($browser) use ($link) {
                        $browser->assertSee('Link')
                                ->assertSee($link->title);
                    });

            $browser->blank();
        });
    }
}
