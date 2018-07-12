<?php

namespace Tests\Browser;

use App\Tag;
use App\Post;
use App\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\IndexComponent;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AttachPolymorphicTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function resource_can_be_attached()
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
                    ->clickAttach()
                    ->assertSee('The tag field is required.');

            $this->assertNull(Post::find(1)->tags->first());
        });
    }
}
