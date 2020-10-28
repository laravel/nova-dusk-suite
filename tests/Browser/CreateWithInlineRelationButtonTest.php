<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Comment;
use App\Models\User;
use Database\Factories\PostFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Tests\DuskTestCase;

class CreateWithInlineRelationButtonTest extends DuskTestCase
{
    /**
     * @test
     */
    public function morph_to_resource_can_be_created_with_attaching_file_to_parent()
    {
        $this->setupLaravel();

        $this->whileInlineCreate(function () {
            $post = PostFactory::new()->create();

            $this->browse(function (Browser $browser) {
                $browser->loginAs(User::find(1))
                        ->visit(new Create('comments'))
                        ->select('@commentable-type', 'posts')
                        ->pause(500)
                        ->runInlineCreate('commentable', function ($browser) {
                            $browser->select('@user', 1)
                                ->type('@title', 'Test Post')
                                ->type('@body', 'Test Post Body')
                                ->attach('@attachment', __DIR__.'/Fixtures/Document.pdf');
                        })
                        ->waitForText('The user post was created!', 10)
                        ->type('@body', 'Test Comment Body')
                        ->create()
                        ->waitForText('The comment was created!', 10);

                $browser->blank();

                $comment = Comment::with('commentable')->latest()->first();
                $this->assertNull($comment->attachment);
                $this->assertNotNull($comment->commentable->attachment);
            });
        });
    }

    /**
     * @test
     */
    public function morph_to_resource_can_be_created_with_attaching_file_to_child()
    {
        $this->setupLaravel();

        $this->whileInlineCreate(function () {
            $post = PostFactory::new()->create();

            $this->browse(function (Browser $browser) {
                $browser->loginAs(User::find(1))
                        ->visit(new Create('comments'))
                        ->select('@commentable-type', 'posts')
                        ->pause(500)
                        ->runInlineCreate('commentable', function ($browser) {
                            $browser->select('@user', 1)
                                ->type('@title', 'Test Post')
                                ->type('@body', 'Test Post Body');
                        })
                        ->waitForText('The user post was created!', 10)
                        ->type('@body', 'Test Comment Body')
                        ->attach('@attachment', __DIR__.'/Fixtures/Document.pdf')
                        ->create()
                        ->waitForText('The comment was created!', 10);

                $browser->blank();

                $comment = Comment::with('commentable')->latest()->first();
                $this->assertNotNull($comment->attachment);
                $this->assertNull($comment->commentable->attachment);
            });
        });
    }
}
