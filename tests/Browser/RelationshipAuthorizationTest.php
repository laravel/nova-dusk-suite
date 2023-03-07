<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\PostFactory;
use Database\Factories\TagFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\Controls\RelationSelectControlComponent;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Attach;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\Index;
use Laravel\Nova\Tests\DuskTestCase;

class RelationshipAuthorizationTest extends DuskTestCase
{
    public function test_resource_cant_be_added_to_parent_if_not_authorized()
    {
        $user = tap(User::find(3), function ($user) {
            $user->shouldBlockFrom('post.create.viaResource');
        });
        User::find(2)->shouldBlockFrom('user.addPost.'.$user->id);
        User::find(1)->shouldBlockFrom('post.create');

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs(3)
                ->visit(new Index('posts'))
                ->within(new IndexComponent('posts'), function (Browser $browser) {
                    $browser->assertVisible('@create-button');
                })
                ->visit(new Detail('users', 2))
                ->within(new IndexComponent('posts'), function (Browser $browser) {
                    $browser->assertMissing('@create-button');
                })
                ->visit(new Detail('users', $user->id))
                ->within(new IndexComponent('posts'), function (Browser $browser) {
                    $browser->assertMissing('@create-button');
                })
                ->visit(new Detail('users', 1))
                ->within(new IndexComponent('posts'), function (Browser $browser) {
                    $browser->assertMissing('@create-button');
                });

            $browser->loginAs(2)
                    ->visit(new Create('posts'))
                    ->whenAvailable(new RelationSelectControlComponent('user'), function ($browser) use ($user) {
                        $browser->assertSelectMissingOptions('', [$user->id, $user->name]);
                    });

            $browser->visit((new Create('posts', [
                'viaResource' => 'users',
                'viaResourceId' => $user->id,
                'viaRelationship' => 'posts',
                'relationshipType' => 'hasMany',
            ]))->url())->assertNotFound();

            $browser->visit(new Index('posts'))
                ->within(new IndexComponent('posts'), function (Browser $browser) {
                    $browser->assertVisible('@create-button');
                })
                ->visit(new Detail('users', 2))
                ->within(new IndexComponent('posts'), function (Browser $browser) {
                    $browser->assertVisible('@create-button');
                })
                ->visit(new Detail('users', $user->id))
                ->within(new IndexComponent('posts'), function (Browser $browser) {
                    $browser->assertMissing('@create-button');
                })
                ->visit(new Detail('users', 1))
                ->within(new IndexComponent('posts'), function (Browser $browser) {
                    $browser->assertVisible('@create-button');
                });

            $browser->loginAs(1)
                ->visit(new Index('posts'))
                ->within(new IndexComponent('posts'), function (Browser $browser) {
                    $browser->assertMissing('@create-button');
                })->visit(new Detail('users', 1))
                ->within(new IndexComponent('posts'), function (Browser $browser) {
                    $browser->assertMissing('@create-button');
                })
                ->visit(new Detail('users', 3))
                ->within(new IndexComponent('posts'), function (Browser $browser) {
                    $browser->assertMissing('@create-button');
                });

            $browser->blank();
        });

        $this->reloadServing();
    }

    public function test_morphable_resource_cant_be_added_to_parent_if_not_authorized()
    {
        $post = PostFactory::new()->create();
        User::find(1)->shouldBlockFrom('post.addComment.'.$post->id);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(1)
                    ->visit(new Create('comments'))
                    ->select('@commentable-type', 'posts')
                    ->whenAvailable(new RelationSelectControlComponent('commentable-select'), function ($browser) use ($post) {
                        $browser->assertSelectMissingOptions('', [$post->id, $post->title]);
                    })
                    ->cancel();

            $browser->blank();
        });
    }

    public function test_create_button_should_be_missing_from_detail_index_when_not_authorized()
    {
        $post = PostFactory::new()->create();
        User::find(1)->shouldBlockFrom('post.addComment.'.$post->id);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new Detail('posts', 1))
                    ->within(new IndexComponent('comments'), function ($browser) {
                        $browser->assertMissing('@create-button');
                    });

            $browser->blank();
        });
    }

    public function test_resource_cant_be_attached_to_parent_if_not_authorized()
    {
        $post = PostFactory::new()->create();
        $tag = TagFactory::new()->create();
        User::find(1)->shouldBlockFrom('post.attachTag.'.$post->id);

        $this->browse(function (Browser $browser) use ($tag) {
            $browser->loginAs(1)
                    ->visit(Attach::morphToMany('posts', 1, 'tags'))
                    ->assertSelectMissingOption('@attachable-select', $tag->name)
                    ->assertSelectMissingOption('@attachable-select', $tag->id);

            $browser->blank();
        });
    }

    public function test_attach_button_should_be_missing_from_detail_index_when_not_authorized()
    {
        $post = PostFactory::new()->create();
        User::find(1)->shouldBlockFrom('post.attachAnyTag.'.$post->id);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new Detail('posts', 1))
                    ->within(new IndexComponent('tags'), function ($browser) {
                        $browser->assertMissing('@attach-button');
                    });

            $browser->blank();
        });
    }
}
