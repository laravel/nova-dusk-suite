<?php

namespace Tests\Browser;

use App\Post;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\IndexComponent;
use Tests\DuskTestCase;

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

        $this->whileSearchable(function () {
            $post = factory(Post::class)->create();

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

    /**
     * @test
     */
    public function morph_to_field_should_honor_custom_labels()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Create('comments'))
                    ->assertSee('User Post')
                    ->assertSee('User Video');
        });
    }
}
