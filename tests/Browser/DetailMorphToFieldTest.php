<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\CommentFactory;
use Database\Factories\LinkFactory;
use Database\Factories\PostFactory;
use Database\Factories\VideoFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\DetailComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class DetailMorphToFieldTest extends DuskTestCase
{
    /**
     * @test
     */
    public function morph_to_field_navigates_to_parent_resource_when_clicked()
    {
        $this->setupLaravel();

        $post = PostFactory::new()->create();
        $post->comments()->save($comment = CommentFactory::new()->make());

        $this->browse(function (Browser $browser) use ($post, $comment) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('comments', $comment->id))
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

        $post = PostFactory::new()->create();
        $post->comments()->save($comment = CommentFactory::new()->make());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('comments', 1))
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

        $video = VideoFactory::new()->create();
        $video->comments()->save($comment = CommentFactory::new()->make());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('comments', 1))
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

        $link = LinkFactory::new()->create();
        $link->comments()->save($comment = CommentFactory::new()->make());

        $this->browse(function (Browser $browser) use ($comment, $link) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('comments', 1))
                    ->within(new DetailComponent('comments', $comment->id), function ($browser) use ($link) {
                        $browser->assertSee('Link')
                                ->assertSee($link->title);
                    });

            $browser->blank();
        });
    }
}
