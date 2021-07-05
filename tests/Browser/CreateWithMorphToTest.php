<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\PostFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class CreateWithMorphToTest extends DuskTestCase
{
    /**
     * @test
     */
    public function resource_can_be_created()
    {
        $this->whileSearchable(function () {
            $post = PostFactory::new()->create();

            $this->browse(function (Browser $browser) use ($post) {
                $browser->loginAs(User::find(1))
                        ->visit(new Create('comments'))
                        ->waitForTextIn('#app [data-testid="content"] form', 'Commentable')
                        ->select('@commentable-type', 'posts')
                        ->pause(500)
                        ->searchAndSelectFirstRelation('commentable', 1)
                        ->type('@body', 'Test Comment')
                        ->create()
                        ->waitForText('The comment was created!')
                        ->on(new Detail('comments', 1));

                $this->assertCount(1, $post->fresh()->comments);

                $browser->blank();
            });
        });
    }

    /**
     * @test
     */
    public function searchable_resource_can_be_created()
    {
        $this->whileSearchable(function () {
            $post = PostFactory::new()->create();

            $this->browse(function (Browser $browser) use ($post) {
                $browser->loginAs(User::find(1))
                        ->visit(new Create('comments'))
                        ->waitForTextIn('#app [data-testid="content"] form', 'Commentable')
                        ->select('@commentable-type', 'posts')
                        ->pause(500)
                        ->searchAndSelectFirstRelation('commentable', 1)
                        ->type('@body', 'Test Comment')
                        ->create()
                        ->waitForText('The comment was created!')
                        ->on(new Detail('comments', 1));

                $this->assertCount(1, $post->fresh()->comments);

                $browser->blank();
            });
        });
    }

    /**
     * @test
     */
    public function non_searchable_resource_can_be_created_via_parent_resource()
    {
        $this->resource_can_be_created_via_parent_resource();
    }

    /**
     * @test
     */
    public function searchable_resource_can_be_created_via_parent_resource()
    {
        $this->whileSearchable(function () {
            $this->resource_can_be_created_via_parent_resource();
        });
    }

    protected function resource_can_be_created_via_parent_resource()
    {
        $post = PostFactory::new()->create();

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('posts', $post->id))
                    ->runCreateRelation('comments')
                    ->waitForTextIn('#app [data-testid="content"] form', 'Commentable')
                    ->assertDisabled('select[dusk="commentable-type"]')
                    ->assertDisabled('select[dusk="commentable-select"]')
                    ->type('@body', 'Test Comment')
                    ->create()
                    ->on(new Detail('comments', 1));

            $this->assertCount(1, $post->fresh()->comments);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function morph_to_field_should_honor_custom_labels()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Create('comments'))
                    ->waitForTextIn('#app [data-testid="content"] form', 'Commentable')
                    ->assertSee('User Post')
                    ->assertSee('User Video');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function morph_to_field_should_honor_query_parameters_on_create()
    {
        $post = PostFactory::new()->create();

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(User::find(1))
                ->visit(new Create('comments', [
                    'viaResource' => 'posts',
                    'viaResourceId' => $post->id,
                    'viaRelationship' => 'comments',
                ]))
                ->waitForTextIn('#app [data-testid="content"] form', 'Commentable')
                ->whenAvailable('select[dusk="commentable-type"]', function ($browser) {
                    $browser->assertDisabled('')
                        ->assertSelected('', 'posts');
                })
                ->whenAvailable('select[dusk="commentable-select"]', function ($browser) use ($post) {
                    $browser->assertDisabled('')
                        ->assertSelected('', $post->id);
                });

            $browser->blank();
        });
    }
}
