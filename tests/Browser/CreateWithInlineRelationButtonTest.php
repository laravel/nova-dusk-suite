<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Comment;
use App\Models\Sail;
use Database\Factories\DockFactory;
use Database\Factories\PostFactory;
use Database\Factories\ShipFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Attach;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Tests\DuskTestCase;

class CreateWithInlineRelationButtonTest extends DuskTestCase
{
    public function test_belongs_to_resource_should_fetch_the_related_resource_id_info()
    {
        $this->defineApplicationStates(['index-query-asc-order', 'inline-create', 'searchable']);

        $this->browse(function (Browser $browser) {
            $dock = DockFactory::new()->create();
            ShipFactory::new()->times(5)->create(['dock_id' => DockFactory::new()->create()]);

            $browser->loginAs(1)
                ->visit(new Create('sails'))
                ->runInlineCreate('ship', function ($browser) use ($dock) {
                    $browser->waitForText('Create Ship')
                        ->searchFirstRelation('docks', $dock->id)
                        ->type('@name', 'Ship name');
                })
                ->waitForText('The ship was created!')
                ->pause(500)
                ->assertSee('Ship name')
                ->type('@inches', 25)
                ->create()
                ->waitForText('The sail was created!');

            $browser->blank();
        });
    }

    public function test_morph_to_resource_can_be_created_with_attaching_file_to_parent()
    {
        $this->defineApplicationStates('inline-create');

        $this->browse(function (Browser $browser) {
            PostFactory::new()->create();

            $browser->loginAs(1)
                    ->visit(new Create('comments'))
                    ->select('@commentable-type', 'posts')
                    ->pause(500)
                    ->runInlineCreate('commentable', function ($browser) {
                        $browser->waitForText('Create User Post')
                            ->selectRelation('user', 1)
                            ->type('@title', 'Test Post')
                            ->type('@body', 'Test Post Body')
                            ->attach('@attachment', __DIR__.'/Fixtures/Document.pdf');
                    })
                    ->waitForText('The user post was created!')
                    ->type('@body', 'Test Comment Body')
                    ->create()
                    ->waitForText('The comment was created!');

            $browser->blank();

            $comment = Comment::with('commentable')->latest()->first();
            $this->assertNull($comment->attachment);
            $this->assertNotNull($comment->commentable->attachment);
        });
    }

    public function test_morph_to_resource_can_be_created_with_attaching_file_to_child()
    {
        $this->defineApplicationStates('inline-create');

        $this->browse(function (Browser $browser) {
            PostFactory::new()->create();

            $browser->loginAs(1)
                    ->visit(new Create('comments'))
                    ->select('@commentable-type', 'posts')
                    ->pause(500)
                    ->runInlineCreate('commentable', function ($browser) {
                        $browser->waitForText('Create User Post')
                            ->selectRelation('user', 1)
                            ->type('@title', 'Test Post')
                            ->type('@body', 'Test Post Body');
                    })
                    ->waitForText('The user post was created!')
                    ->type('@body', 'Test Comment Body')
                    ->attach('@attachment', __DIR__.'/Fixtures/Document.pdf')
                    ->create()
                    ->waitForText('The comment was created!');

            $browser->blank();

            $comment = Comment::with('commentable')->latest()->first();
            $this->assertNotNull($comment->attachment);
            $this->assertNull($comment->commentable->attachment);
        });
    }

    public function test_belongs_to_many_resource_should_fetch_the_related_resource_id_info()
    {
        $this->defineApplicationStates(['index-query-asc-order', 'inline-create']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(Attach::belongsToMany('users', 1, 'roles'))
                ->runInlineCreate('roles', function ($browser) {
                    $browser->waitForText('Create Role')
                        ->type('@name', 'Manager');
                })
                ->waitForText('The role was created!')
                ->pause(500)
                ->assertSee('Manager')
                ->create()
                ->waitForText('The resource was attached!');

            $browser->blank();
        });
    }

    public function test_slug_not_affected_by_create_relation_modal()
    {
        $this->defineApplicationStates('inline-create');

        $this->browse(function (Browser $browser) {
            $dock = DockFactory::new()->create();

            $browser->loginAs(1)
                    ->visit(new Create('sails'))
                    ->keys('@name', 'Test Sail', '{tab}')
                    ->type('@inches', 350)
                    ->runInlineCreate('ship', function ($browser) use ($dock) {
                        $browser->waitForText('Create Ship')
                            ->searchFirstRelation('docks', $dock->id)
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
    }
}
