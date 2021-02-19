<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Post;
use App\Models\User;
use Database\Factories\PostFactory;
use Database\Factories\TagFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Attach;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class AttachPolymorphicTest extends DuskTestCase
{
    /**
     * @test
     */
    public function non_searchable_resource_can_be_attached()
    {
        $this->whileSearchable(function () {
            $post = PostFactory::new()->create();
            $tag = TagFactory::new()->create();

            $this->browse(function (Browser $browser) use ($tag) {
                $browser->loginAs(User::find(1))
                        ->visit(new Detail('posts', 1))
                        ->within(new IndexComponent('tags'), function ($browser) {
                            $browser->waitFor('@attach-button')
                                    ->click('@attach-button');
                        })
                        ->on(new Attach('posts', 1, 'tags'))
                        ->searchAndSelectFirstRelation('tags', $tag->id)
                        ->clickAttach();

                $this->assertEquals($tag->id, Post::find(1)->tags->first()->id);

                $browser->blank();
            });
        });
    }

    /**
     * @test
     */
    public function searchable_resource_can_be_attached()
    {
        $this->whileSearchable(function () {
            $post = PostFactory::new()->create();
            $tag = TagFactory::new()->create();

            $this->browse(function (Browser $browser) use ($tag) {
                $browser->loginAs(User::find(1))
                        ->visit(new Detail('posts', 1))
                        ->within(new IndexComponent('tags'), function ($browser) {
                            $browser->waitFor('@attach-button')
                                    ->click('@attach-button');
                        })
                        ->on(new Attach('posts', 1, 'tags'))
                        ->searchAndSelectFirstRelation('tags', $tag->id)
                        ->clickAttach();

                $this->assertEquals($tag->id, Post::find(1)->tags->first()->id);

                $browser->blank();
            });
        });
    }

    /**
     * @test
     */
    public function fields_on_intermediate_table_should_be_stored()
    {
        $this->whileSearchable(function () {
            $post = PostFactory::new()->create();
            $tag = TagFactory::new()->create();

            $this->browse(function (Browser $browser) use ($tag) {
                $browser->loginAs(User::find(1))
                        ->visit(new Detail('posts', 1))
                        ->within(new IndexComponent('tags'), function ($browser) {
                            $browser->waitFor('@attach-button')
                                    ->click('@attach-button');
                        })
                        ->on(new Attach('posts', 1, 'tags'))
                        ->searchAndSelectFirstRelation('tags', $tag->id)
                        ->type('@notes', 'Test Notes')
                        ->clickAttach();

                $this->assertEquals($tag->id, Post::find(1)->tags->first()->id);
                $this->assertEquals('Test Notes', Post::find(1)->tags->first()->pivot->notes);

                $browser->blank();
            });
        });
    }

    /**
     * @test
     */
    public function validation_errors_are_displayed()
    {
        $post = PostFactory::new()->create();
        $tag = TagFactory::new()->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('posts', 1))
                    ->within(new IndexComponent('tags'), function ($browser) {
                        $browser->waitFor('@attach-button')
                                ->click('@attach-button');
                    })
                    ->on(new Attach('posts', 1, 'tags'))
                    ->type('@notes', str_repeat('A', 30))
                    ->clickAttach()
                    ->waitForText('There was a problem submitting the form.', 15)
                    ->assertSee('The tag field is required.');

            $this->assertNull(Post::find(1)->tags->first());

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function validation_errors_are_displayed_for_pivot_fields()
    {
        $this->whileSearchable(function () {
            $post = PostFactory::new()->create();
            $tag = TagFactory::new()->create();

            $this->browse(function (Browser $browser) use ($tag) {
                $browser->loginAs(User::find(1))
                        ->visit(new Detail('posts', 1))
                        ->within(new IndexComponent('tags'), function ($browser) {
                            $browser->waitFor('@attach-button')
                                    ->click('@attach-button');
                        })
                        ->on(new Attach('posts', 1, 'tags'))
                        ->searchAndSelectFirstRelation('tags', $tag->id)
                        ->type('@notes', str_repeat('A', 30))
                        ->clickAttach()
                        ->assertSee('The notes may not be greater than 20 characters.');

                $this->assertNull(Post::find(1)->tags->first());

                $browser->blank();
            });
        });
    }
}
