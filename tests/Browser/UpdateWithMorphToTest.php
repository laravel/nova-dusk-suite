<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Post;
use Database\Factories\CommentFactory;
use Database\Factories\LinkFactory;
use Database\Factories\PostFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\FormComponent;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Tests\DuskTestCase;

class UpdateWithMorphToTest extends DuskTestCase
{
    public function test_resource_can_be_updated_to_new_parent()
    {
        $this->defineApplicationStates('searchable');

        $this->browse(function (Browser $browser) {
            $comment = CommentFactory::new()->create();
            PostFactory::new()->create();

            $browser->loginAs(1)
                    ->visit(new Update('comments', $comment->id))
                    ->within(new FormComponent(), function ($browser) {
                        $browser->searchFirstRelation('commentable', 2);
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
        $this->defineApplicationStates('searchable');

        $this->browse(function (Browser $browser) {
            $link = LinkFactory::new()->create();
            $link->comments()->save($comment = CommentFactory::new()->make());

            $browser->loginAs(1)
                ->visit(new Update('comments', $comment->id))
                ->within(new FormComponent(), function ($browser) {
                    $browser->assertEnabled('select[dusk="commentable-type"]')
                        ->within('select[dusk="commentable-type"]', function ($browser) {
                            $browser->assertSee('Link');
                        });
                })
                ->assertSelectedSearchResult('commentable', $link->title);

            $browser->blank();
        });
    }

    public function test_morph_to_field_should_ignore_query_parameters_when_editing()
    {
        $this->defineApplicationStates('searchable');

        $this->browse(function (Browser $browser) {
            $post = PostFactory::new()->create();
            $link = LinkFactory::new()->create();
            $post->comments()->save($comment = CommentFactory::new()->make());

            $browser->loginAs(1)
                ->visit(new Update('comments', $comment->id, [
                    'viaResource' => 'links',
                    'viaResourceId' => 1,
                    'viaRelationship' => 'comments',
                ]))
                ->within(new FormComponent(), function ($browser) use ($post) {
                    $browser->whenAvailable('select[dusk="commentable-type"]', function ($browser) {
                        $browser->assertEnabled('')
                                ->assertSelected('', 'posts');
                    })
                    ->assertSelectedSearchResult('commentable', $post->title);
                });

            $browser->blank();
        });
    }
}
