<?php

namespace Tests\Browser;

use App\Post;
use App\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\IndexComponent;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class CreateWithMorphToTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function resource_can_be_created()
    {
        $this->seed();

        $post = factory(Post::class)->create();

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Create('comments'))
                    ->select('@commentable-type', 'posts')
                    ->pause(500)
                    ->select('@commentable-select', 1)
                    ->type('@body', 'Test Comment')
                    ->create();

            $browser->assertPathIs('/nova/resources/comments/1');

            $this->assertCount(1, $post->fresh()->comments);
        });
    }

    /**
     * @test
     */
    public function searchable_resource_can_be_created()
    {
        $this->seed();

        touch(base_path('.searchable'));

        $post = factory(Post::class)->create();

        try {
            $this->browse(function (Browser $browser) use ($post) {
                $browser->loginAs(User::find(1))
                        ->visit(new Pages\Create('comments'))
                        ->select('@commentable-type', 'posts')
                        ->pause(500)
                        ->searchRelation('commentable', 1)
                        ->selectCurrentRelation('commentable')
                        ->type('@body', 'Test Comment')
                        ->create();

                $browser->assertPathIs('/nova/resources/comments/1');

                $this->assertCount(1, $post->fresh()->comments);
            });
        } finally {
            @unlink(base_path('.searchable'));
        }
    }

    /**
     * @test
     */
    public function resource_can_be_created_via_parent_resource()
    {
        $this->seed();

        $post = factory(Post::class)->create();

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('posts', $post->id))
                    ->within(new IndexComponent('comments'), function ($browser) {
                        $browser->click('@create-button');
                    })
                    ->on(new Pages\Create('comments'))
                    ->assertDisabled('@commentable-type')
                    ->assertDisabled('@commentable-select')
                    ->type('@body', 'Test Comment')
                    ->create();

            $browser->assertPathIs('/nova/resources/comments/1');

            $this->assertCount(1, $post->fresh()->comments);
        });
    }
}
