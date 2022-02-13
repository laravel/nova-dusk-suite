<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Comment;
use App\Models\Sail;
use Database\Factories\DockFactory;
use Database\Factories\PostFactory;
use Database\Factories\ShipFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Tests\DuskTestCase;

class CreateWithInlineRelationButtonTest extends DuskTestCase
{
    /**
     * @test
     */
    public function belongs_to_resource_should_fetch_the_related_resource_id_info()
    {
        $this->whileIndexQueryAscOrder(function () {
            $this->whileInlineCreate(function () {
                $this->whileSearchable(function () {
                    $dock = DockFactory::new()->create();
                    ShipFactory::new()->count(5)->create();

                    $this->browse(function (Browser $browser) use ($dock) {
                        $browser->loginAs(1)
                            ->visit(new Create('sails'))
                            ->runInlineCreate('ship', function ($browser) use ($dock) {
                                $browser->waitForText('Create Ship', 25)
                                    ->searchAndSelectFirstRelation('docks', $dock->id)
                                    ->type('@name', 'Ship name');
                            })
                            ->waitForText('The ship was created!', 25)
                            ->pause(500)
                            ->assertSee('Ship name')
                            ->type('@inches', 25)
                            ->create()
                            ->waitForText('The sail was created!', 25);
                    });
                });
            });
        });
    }

    /**
     * @test
     */
    public function morph_to_resource_can_be_created_with_attaching_file_to_parent()
    {
        $this->whileInlineCreate(function () {
            PostFactory::new()->create();

            $this->browse(function (Browser $browser) {
                $browser->loginAs(1)
                        ->visit(new Create('comments'))
                        ->select('@commentable-type', 'posts')
                        ->pause(500)
                        ->runInlineCreate('commentable', function ($browser) {
                            $browser->waitForText('Create User Post', 25)
                                ->select('@user', 1)
                                ->type('@title', 'Test Post')
                                ->type('@body', 'Test Post Body')
                                ->attach('@attachment', __DIR__.'/Fixtures/Document.pdf');
                        })
                        ->waitForText('The user post was created!', 25)
                        ->type('@body', 'Test Comment Body')
                        ->create()
                        ->waitForText('The comment was created!', 25);

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
        $this->whileInlineCreate(function () {
            PostFactory::new()->create();

            $this->browse(function (Browser $browser) {
                $browser->loginAs(1)
                        ->visit(new Create('comments'))
                        ->select('@commentable-type', 'posts')
                        ->pause(500)
                        ->runInlineCreate('commentable', function ($browser) {
                            $browser->waitForText('Create User Post', 25)
                                ->select('@user', 1)
                                ->type('@title', 'Test Post')
                                ->type('@body', 'Test Post Body');
                        })
                        ->waitForText('The user post was created!', 25)
                        ->type('@body', 'Test Comment Body')
                        ->attach('@attachment', __DIR__.'/Fixtures/Document.pdf')
                        ->create()
                        ->waitForText('The comment was created!', 25);

                $browser->blank();

                $comment = Comment::with('commentable')->latest()->first();
                $this->assertNotNull($comment->attachment);
                $this->assertNull($comment->commentable->attachment);
            });
        });
    }

    /**
     * @test
     */
    public function slug_not_affected_by_create_relation_modal()
    {
        $this->whileInlineCreate(function () {
            $this->browse(function (Browser $browser) {
                $dock = DockFactory::new()->create();

                $browser->loginAs(1)
                        ->visit(new Create('sails'))
                        ->waitFor('.content form')
                        ->keys('@name', 'Test Sail', '{tab}')
                        ->type('@inches', 350)
                        ->runInlineCreate('ship', function ($browser) use ($dock) {
                            $browser->waitForText('Create Ship')
                                ->searchAndSelectFirstRelation('docks', $dock->id)
                                ->keys('@name', 'Test Ship', '{tab}');
                        })
                        ->waitForText('The ship was created!')
                        ->pause(500)
                        ->create()
                        ->waitForText('The sail was created!');

                $sail = Sail::latest()->first();
                $this->assertSame('Test Sail', $sail->name);
                $this->assertSame('test-sail', $sail->slug);

                $browser->blank();
            });
        });
    }
}
