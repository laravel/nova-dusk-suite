<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\CommentFactory;
use Database\Factories\LinkFactory;
use Database\Factories\PostFactory;
use Database\Factories\UserFactory;
use Database\Factories\VideoFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\DetailComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class DetailMorphToFieldTest extends DuskTestCase
{
    public function test_morph_to_field_navigates_to_parent_resource_when_clicked()
    {
        $post = PostFactory::new()->create();
        $post->comments()->save($comment = CommentFactory::new()->make());

        $this->browse(function (Browser $browser) use ($post, $comment) {
            $browser->loginAs(1)
                ->visit(new Detail('comments', $comment->id))
                ->within(new DetailComponent('comments', $comment->id), function ($browser) use ($post) {
                    $browser->waitForText('Comment Details', 15)
                        ->assertSee('Post')
                        ->clickLink($post->title);
                })
                ->on(new Detail('posts', $post->id))
                ->assertSee('User Post Details: '.$post->id);

            $browser->blank();
        });
    }

    public function test_morph_to_field_should_honor_custom_labels()
    {
        $post = PostFactory::new()->create();
        $post->comments()->save(CommentFactory::new()->make());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('comments', 1))
                ->waitForText('Comment Details', 15)
                ->assertSee('User Post');

            $browser->blank();
        });
    }

    public function test_morph_to_field_should_honor_custom_labels_again()
    {
        $video = VideoFactory::new()->create();
        $video->comments()->save($comment = CommentFactory::new()->make());

        $this->browse(function (Browser $browser) use ($comment) {
            $browser->loginAs(1)
                ->visit(new Detail('comments', $comment->id))
                ->assertSee('User Video');

            $browser->blank();
        });
    }

    public function test_morph_to_field_should_honor_custom_polymorphic_type()
    {
        $link = LinkFactory::new()->create();
        $link->comments()->save($comment = CommentFactory::new()->make());

        $this->browse(function (Browser $browser) use ($comment, $link) {
            $browser->loginAs(1)
                ->visit(new Detail('comments', $comment->id))
                ->within(new DetailComponent('comments', $comment->id), function ($browser) use ($link) {
                    $browser->assertSee('Link')
                        ->assertSee($link->title);
                });

            $browser->blank();
        });
    }

    public function test_morph_to_field_can_be_displayed_when_not_defined_using_types()
    {
        $comment = CommentFactory::new()->create([
            'commentable_type' => \Illuminate\Foundation\Auth\User::class,
            'commentable_id' => 4,
        ]);

        $this->browse(function (Browser $browser) use ($comment) {
            $browser->loginAs(1)
                ->visit(new Detail('comments', $comment->id))
                ->waitForTextIn('h1', 'Comment Details')
                ->within(new DetailComponent('comments', $comment->id), function ($browser) use ($comment) {
                    $browser->assertSee('Illuminate\Foundation\Auth\User: '.$comment->commentable->id);
                });

            $browser->blank();
        });
    }

    public function test_morph_to_field_accepts_parent_with_big_int_id()
    {
        $post = PostFactory::new()->create([
            'id' => 9121018173229432287,
        ]);
        $post->comments()->save($comment = CommentFactory::new()->make());

        $this->browse(function (Browser $browser) use ($post, $comment) {
            $browser->loginAs(1)
                ->visit(new Detail('comments', $comment->id))
                ->waitForTextIn('h1', 'Comment Details')
                ->within(new DetailComponent('comments', $comment->id), function ($browser) use ($post) {
                    $browser->clickLink($post->title)->pause(2000);
                })
                ->waitForRoute('nova.pages.detail', ['resource' => 'posts', 'resourceId' => $post->id])
                ->waitForTextIn('h1', 'User Post Details')
                ->assertSee('User Post Details: '.$post->id);

            $browser->blank();
        });
    }

    public function test_morph_to_field_accepts_not_defined_using_types_parent_with_big_int_id()
    {
        UserFactory::new()->create([
            'id' => 9121018173229432288,
        ]);

        $comment = CommentFactory::new()->create([
            'commentable_type' => \Illuminate\Foundation\Auth\User::class,
            'commentable_id' => 9121018173229432288,
        ]);

        $this->browse(function (Browser $browser) use ($comment) {
            $browser->loginAs(1)
                ->visit(new Detail('comments', $comment->id))
                ->within(new DetailComponent('comments', $comment->id), function ($browser) use ($comment) {
                    $browser->waitForText('Commentable')
                        ->assertSee('Illuminate\Foundation\Auth\User: '.$comment->commentable->id);
                });

            $browser->blank();
        });

        $this->assertSame(9121018173229432288, $comment->commentable->id);
    }
}
