<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Post;
use App\Models\User;
use Database\Factories\CommentFactory;
use Database\Factories\PostFactory;
use Database\Factories\TagFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Forbidden;
use Laravel\Nova\Testing\Browser\Pages\Index;
use Laravel\Nova\Testing\Browser\Pages\UpdateAttached;
use Laravel\Nova\Tests\DuskTestCase;

class UpdateAttachedPolymorphicTest extends DuskTestCase
{
    /**
     * @test
     */
    public function attached_resource_can_be_updated()
    {
        $post = PostFactory::new()->create();
        $tag = TagFactory::new()->create();
        $post->tags()->attach($tag, ['notes' => 'Test Notes']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new UpdateAttached('posts', 1, 'tags', 1))
                    ->assertDisabled('select[dusk="attachable-select"]')
                    ->assertInputValue('@notes', 'Test Notes')
                    ->type('@notes', 'Test Notes Updated')
                    ->update();

            $this->assertEquals('Test Notes Updated', Post::find(1)->tags->first()->pivot->notes);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function attached_searchable_resource_is_locked()
    {
        $this->whileSearchable(function () {
            $post = PostFactory::new()->create();
            $tag = TagFactory::new()->create();
            $post->tags()->attach($tag, ['notes' => 'Test Notes']);

            $this->browse(function (Browser $browser) {
                $browser->loginAs(User::find(1))
                        ->visit(new UpdateAttached('posts', 1, 'tags', 1))
                        ->assertDisabled('select[dusk="attachable-select"]')
                        ->whenAvailable('@notes', function ($browser) {
                            $browser->assertInputValue('', 'Test Notes')
                                    ->type('', 'Test Notes Updated');
                        })
                        ->update();

                $this->assertEquals('Test Notes Updated', Post::find(1)->tags->first()->pivot->notes);

                $browser->blank();
            });
        });
    }

    /**
     * @test
     */
    public function attached_resource_can_be_updated_and_can_continue_editing()
    {
        $post = PostFactory::new()->create();
        $tag = TagFactory::new()->create();
        $post->tags()->attach($tag, ['notes' => 'Test Notes']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new UpdateAttached('posts', 1, 'tags', 1))
                    ->whenAvailable('@notes', function ($browser) {
                        $browser->type('', 'Test Notes Updated');
                    })
                    ->updateAndContinueEditing()
                    ->waitForText('The resource was updated!')
                    ->on(new UpdateAttached('posts', 1, 'tags', 1));

            $this->assertEquals('Test Notes Updated', Post::find(1)->tags->first()->pivot->notes);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function validation_errors_are_displayed()
    {
        $post = PostFactory::new()->create();
        $tag = TagFactory::new()->create();
        $post->tags()->attach($tag, ['notes' => 'Test Notes']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new UpdateAttached('posts', 1, 'tags', 1))
                    ->whenAvailable('@notes', function ($browser) {
                        $browser->type('', str_repeat('A', 30));
                    })
                    ->update()
                    ->assertSee('The notes may not be greater than 20 characters.');

            $this->assertEquals('Test Notes', Post::find(1)->tags->first()->pivot->notes);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function it_cant_edit_unsupported_polymorphic_relationship_type()
    {
        $comment = CommentFactory::new()->create([
            'commentable_type' => \Illuminate\Foundation\Auth\User::class,
            'commentable_id' => 4,
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Index('comments'))
                    ->within(new IndexComponent('comments'), function ($browser) {
                        $browser->waitForTable()
                            ->click('@1-edit-button');
                    })
                    ->on(new Forbidden);

            $browser->blank();
        });
    }
}
