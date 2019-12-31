<?php

namespace Tests\Browser;

use App\Post;
use App\Tag;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\IndexComponent;
use Tests\DuskTestCase;

class AttachPolymorphicTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function non_searchable_resource_can_be_attached()
    {
        $this->seed();

        $post = factory(Post::class)->create();
        $tag = factory(Tag::class)->create();

        $this->browse(function (Browser $browser) use ($post, $tag) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('posts', 1))
                    ->within(new IndexComponent('tags'), function ($browser) {
                        $browser->click('@attach-button');
                    })
                    ->on(new Pages\Attach('posts', 1, 'tags'))
                    ->selectAttachable($tag->id)
                    ->clickAttach();

            $this->assertEquals($tag->id, Post::find(1)->tags->first()->id);
        });
    }

    /**
     * @test
     */
    public function searchable_resource_can_be_attached()
    {
        $this->seed();

        $this->whileSearchable(function () {
            $post = factory(Post::class)->create();
            $tag = factory(Tag::class)->create();

            $this->browse(function (Browser $browser) use ($post, $tag) {
                $browser->loginAs(User::find(1))
                        ->visit(new Pages\Detail('posts', 1))
                        ->within(new IndexComponent('tags'), function ($browser) {
                            $browser->click('@attach-button');
                        })
                        ->on(new Pages\Attach('posts', 1, 'tags'))
                        ->searchRelation('tags', $tag->id)
                        ->selectCurrentRelation('tags')
                        ->clickAttach();

                $this->assertEquals($tag->id, Post::find(1)->tags->first()->id);
            });
        });
    }

    /**
     * @test
     */
    public function fields_on_intermediate_table_should_be_stored()
    {
        $this->seed();

        $post = factory(Post::class)->create();
        $tag = factory(Tag::class)->create();

        $this->browse(function (Browser $browser) use ($post, $tag) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('posts', 1))
                    ->within(new IndexComponent('tags'), function ($browser) {
                        $browser->click('@attach-button');
                    })
                    ->on(new Pages\Attach('posts', 1, 'tags'))
                    ->selectAttachable($tag->id)
                    ->type('@notes', 'Test Notes')
                    ->clickAttach();

            $this->assertEquals($tag->id, Post::find(1)->tags->first()->id);
            $this->assertEquals('Test Notes', Post::find(1)->tags->first()->pivot->notes);
        });
    }

    /**
     * @test
     */
    public function validation_errors_are_displayed()
    {
        $this->seed();

        $post = factory(Post::class)->create();
        $tag = factory(Tag::class)->create();

        $this->browse(function (Browser $browser) use ($post, $tag) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('posts', 1))
                    ->within(new IndexComponent('tags'), function ($browser) {
                        $browser->click('@attach-button');
                    })
                    ->on(new Pages\Attach('posts', 1, 'tags'))
                    ->type('@notes', str_repeat('A', 30))
                    ->clickAttach()
                    ->assertSee('The tag field is required.');

            $this->assertNull(Post::find(1)->tags->first());
        });
    }

    /**
     * @test
     */
    public function validation_errors_are_displayed_for_pivot_fields()
    {
        $this->seed();

        $post = factory(Post::class)->create();
        $tag = factory(Tag::class)->create();

        $this->browse(function (Browser $browser) use ($post, $tag) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('posts', 1))
                    ->within(new IndexComponent('tags'), function ($browser) {
                        $browser->click('@attach-button');
                    })
                    ->on(new Pages\Attach('posts', 1, 'tags'))
                    ->selectAttachable($tag->id)
                    ->type('@notes', str_repeat('A', 30))
                    ->clickAttach()
                    ->assertSee('The notes may not be greater than 20 characters.');

            $this->assertNull(Post::find(1)->tags->first());
        });
    }
}
