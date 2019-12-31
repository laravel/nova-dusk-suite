<?php

namespace Tests\Browser;

use App\Post;
use App\Tag;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class UpdateAttachedPolymorphicTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function attached_resource_can_be_updated()
    {
        $this->seed();

        $post = factory(Post::class)->create();
        $tag = factory(Tag::class)->create();
        $post->tags()->attach($tag, ['notes' => 'Test Notes']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\UpdateAttached('posts', 1, 'tags', 1))
                    ->assertDisabled('@attachable-select')
                    ->assertInputValue('@notes', 'Test Notes')
                    ->type('@notes', 'Test Notes Updated')
                    ->update();

            $this->assertEquals('Test Notes Updated', Post::find(1)->tags->first()->pivot->notes);
        });
    }

    /**
     * @test
     */
    public function attached_searchable_resource_is_locked()
    {
        $this->seed();

        $this->whileSearchable(function () {
            $post = factory(Post::class)->create();
            $tag = factory(Tag::class)->create();
            $post->tags()->attach($tag, ['notes' => 'Test Notes']);

            $this->browse(function (Browser $browser) {
                $browser->loginAs(User::find(1))
                        ->visit(new Pages\UpdateAttached('posts', 1, 'tags', 1))
                        ->assertDisabled('@attachable-select')
                        ->assertInputValue('@notes', 'Test Notes')
                        ->type('@notes', 'Test Notes Updated')
                        ->update();

                $this->assertEquals('Test Notes Updated', Post::find(1)->tags->first()->pivot->notes);
            });
        });
    }

    /**
     * @test
     */
    public function attached_resource_can_be_updated_and_can_continue_editing()
    {
        $this->seed();

        $post = factory(Post::class)->create();
        $tag = factory(Tag::class)->create();
        $post->tags()->attach($tag, ['notes' => 'Test Notes']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\UpdateAttached('posts', 1, 'tags', 1))
                    ->type('@notes', 'Test Notes Updated')
                    ->updateAndContinueEditing();

            $browser->assertPathIs('/nova/resources/posts/1/edit-attached/tags/1');

            $this->assertEquals('Test Notes Updated', Post::find(1)->tags->first()->pivot->notes);
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
        $post->tags()->attach($tag, ['notes' => 'Test Notes']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\UpdateAttached('posts', 1, 'tags', 1))
                    ->type('@notes', str_repeat('A', 30))
                    ->update()
                    ->assertSee('The notes may not be greater than 20 characters.');

            $this->assertEquals('Test Notes', Post::find(1)->tags->first()->pivot->notes);
        });
    }
}
