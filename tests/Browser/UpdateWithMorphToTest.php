<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Post;
use Database\Factories\CommentFactory;
use Database\Factories\CompanyFactory;
use Database\Factories\LinkFactory;
use Database\Factories\PhotoFactory;
use Database\Factories\PostFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\BreadcrumbComponent;
use Laravel\Nova\Testing\Browser\Components\Controls\RelationSelectControlComponent;
use Laravel\Nova\Testing\Browser\Components\FormComponent;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Tests\DuskTestCase;

class UpdateWithMorphToTest extends DuskTestCase
{
    public function test_resource_can_be_updated_to_new_parent()
    {
        $comment = CommentFactory::new()->create();
        PostFactory::new()->create();

        $this->browse(function (Browser $browser) use ($comment) {
            $browser->loginAs(1)
                ->visit(new Update('comments', $comment->id))
                ->assertMissing('@commentable-unlock-relation')
                ->selectRelation('commentable', 2)
                ->assertMissing('@commentable-unlock-relation')
                ->update()
                ->waitForText('The comment was updated');

            $this->assertSame(0, Post::withCount('comments')->find(1)->comments_count);
            $this->assertSame(1, Post::withCount('comments')->find(2)->comments_count);

            $browser->blank();
        });
    }

    public function test_resource_can_be_updated_to_new_parent_using_searchable()
    {
        $this->defineApplicationStates('searchable');

        $this->browse(function (Browser $browser) {
            $comment = CommentFactory::new()->create();
            PostFactory::new()->create();

            $browser->loginAs(1)
                ->visit(new Update('comments', $comment->id))
                ->within(new BreadcrumbComponent(), function ($browser) use ($comment) {
                    $browser->assertSeeLink('Comments')
                        ->assertSeeLink('Comment Details: '.$comment->id)
                        ->assertCurrentPageTitle('Update Comment');
                })
                ->within(new FormComponent(), function ($browser) {
                    $browser->assertMissing('@commentable-unlock-relation')
                        ->searchFirstRelation('commentable', 2)
                        ->assertMissing('@commentable-unlock-relation');
                })
                ->update()
                ->waitForText('The comment was updated');

            $posts = Post::withCount('comments')->findMany([1, 2], ['id']);

            $this->assertSame(0, $posts->find(1)->comments_count);
            $this->assertSame(1, $posts->find(2)->comments_count);

            $browser->blank();
        });
    }

    public function test_morph_to_field_should_honor_custom_polymorphic_type()
    {
        $link = LinkFactory::new()->create();
        $link->comments()->save($comment = CommentFactory::new()->create());

        $this->browse(function (Browser $browser) use ($comment, $link) {
            $browser->loginAs(1)
                ->visit(new Update('comments', $comment->id))
                ->assertEnabled('@commentable-type')
                ->within('@commentable-type', function ($browser) {
                    $browser->assertSee('Link');
                })
                ->whenAvailable(new RelationSelectControlComponent('commentable'), function ($browser) use ($link) {
                    $browser->assertSelected('', $link->id);
                });

            $browser->blank();
        });
    }

    public function test_morph_to_field_should_honor_custom_polymorphic_type_using_searchable()
    {
        $this->defineApplicationStates('searchable');

        $this->browse(function (Browser $browser) {
            $link = LinkFactory::new()->create();
            $link->comments()->save($comment = CommentFactory::new()->make());

            $browser->loginAs(1)
                ->visit(new Update('comments', $comment->id))
                ->within(new BreadcrumbComponent(), function ($browser) use ($comment) {
                    $browser->assertSeeLink('Comments')
                        ->assertSeeLink('Comment Details: '.$comment->id)
                        ->assertCurrentPageTitle('Update Comment');
                })
                ->within(new FormComponent(), function ($browser) {
                    $browser->assertEnabled('select[dusk="commentable-type"]')
                        ->within('select[dusk="commentable-type"]', function ($browser) {
                            $browser->assertSee('Link');
                        });
                })
                ->assertSelectedFirstSearchResult('commentable', $link->title);

            $browser->blank();
        });
    }

    public function test_morph_to_field_should_ignore_query_parameters_when_editing()
    {
        $post = PostFactory::new()->create();
        $post->comments()->save($comment = CommentFactory::new()->create());

        $this->browse(function (Browser $browser) use ($comment, $post) {
            $browser->loginAs(1)
                ->visit(new Update('comments', $comment->id, [
                    'viaResource' => 'links',
                    'viaResourceId' => $post->id,
                    'viaRelationship' => 'comments',
                ]))
                ->whenAvailable('@commentable-type', function ($browser) {
                    $browser->assertEnabled('')
                        ->assertSelected('', 'posts');
                })
                ->whenAvailable(new RelationSelectControlComponent('commentable'), function ($browser) use ($post) {
                    $browser->assertSelected('', $post->id);
                });

            // It can reset the value.
            $browser->visit(new Update('comments', $comment->id, [
                'viaResource' => 'posts',
                'viaResourceId' => $post->id,
                'viaRelationship' => 'comments',
            ]))
                ->assertQueryStringHas('viaResource', 'posts')
                ->assertQueryStringHas('viaResourceId', $post->id)
                ->assertQueryStringHas('viaRelationship', 'comments')
                ->resetSearchRelation('commentable')
                ->whenAvailable('@commentable-type', function ($browser) {
                    $browser->assertEnabled('')
                        ->assertSelected('', 'posts');
                })
                ->whenAvailable(new RelationSelectControlComponent('commentable'), function ($browser) use ($post) {
                    $browser->assertSelected('', $post->id);
                })
                ->assertQueryStringMissing('viaResource')
                ->assertQueryStringMissing('viaResourceId')
                ->assertQueryStringMissing('viaRelationship');

            $browser->blank();
        });
    }

    public function test_morph_to_field_should_ignore_query_parameters_when_editing_using_searchable()
    {
        $this->defineApplicationStates('searchable');

        $this->browse(function (Browser $browser) {
            $post = PostFactory::new()->create();
            $link = LinkFactory::new()->create();
            $post->comments()->save($comment = CommentFactory::new()->make());

            $browser->loginAs(1)
                ->visit(new Update('comments', $comment->id, [
                    'viaResource' => 'links',
                    'viaResourceId' => $post->id,
                    'viaRelationship' => 'comments',
                ]))
                ->within(new BreadcrumbComponent(), function ($browser) {
                    $browser->assertSeeLink('Link Details: 1')
                        ->assertCurrentPageTitle('Update Comment');
                })
                ->within(new FormComponent(), function ($browser) use ($post) {
                    $browser->whenAvailable('select[dusk="commentable-type"]', function ($browser) {
                        $browser->assertEnabled('')
                            ->assertSelected('', 'posts');
                    })
                        ->assertSelectedSearchResult('commentable', $post->title);
                });

            // It can reset the value.
            $browser->visit(new Update('comments', $comment->id, [
                'viaResource' => 'posts',
                'viaResourceId' => $post->id,
                'viaRelationship' => 'comments',
            ]))
                ->assertQueryStringHas('viaResource', 'posts')
                ->assertQueryStringHas('viaResourceId', 1)
                ->assertQueryStringHas('viaRelationship', 'comments')
                ->resetSearchRelation('commentable')
                ->whenAvailable('@commentable-type', function ($browser) {
                    $browser->assertEnabled('')
                        ->assertSelected('', 'posts');
                })
                ->assertSearchResultContains('commentable', $post->title)
                ->assertQueryStringMissing('viaResource')
                ->assertQueryStringMissing('viaResourceId')
                ->assertQueryStringMissing('viaRelationship');

            $browser->blank();
        });
    }

    public function test_morph_to_fields_can_be_set_to_null()
    {
        $this->browse(function (Browser $browser) {
            $company = CompanyFactory::new()->create();
            $photo = PhotoFactory::new()->create([
                'imageable_type' => $company->getMorphClass(),
                'imageable_id' => $company->getKey(),
                'url' => 'avatar.jpg',
            ]);

            $browser->loginAs(1)
                ->visit(new Update('photos', $photo->id))
                ->within(new FormComponent(), function ($browser) {
                    $browser->selectRelation('imageable', '');
                })->update()
                ->waitForText('The photo was updated!');

            $this->assertDatabaseHas('photos', [
                'id' => $photo->getKey(),
                'imageable_type' => null,
                'imageable_id' => null,
                'url' => 'avatar.jpg',
            ]);

            $browser->blank();
        });
    }
}
