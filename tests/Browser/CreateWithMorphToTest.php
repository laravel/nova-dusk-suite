<?php

namespace Laravel\Nova\Tests\Browser;

use App\Post;
use App\User;
use Laravel\Dusk\Browser;
use Laravel\Nova\Tests\Browser\Components\IndexComponent;
use Laravel\Nova\Tests\DuskTestCase;

class CreateWithMorphToTest extends DuskTestCase
{
    /**
     * @test
     */
    public function resource_can_be_created()
    {
        $this->setupLaravel();

        $post = factory(Post::class)->create();

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Create('comments'))
                    ->select('@commentable-type', 'posts')
                    ->pause(500)
                    ->searchAndSelectFirstRelation('commentable', 1)
                    ->type('@body', 'Test Comment')
                    ->create();

            $browser->assertPathIs('/nova/resources/comments/1');

            $this->assertCount(1, $post->fresh()->comments);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function searchable_resource_can_be_created()
    {
        $this->setupLaravel();

        $this->whileSearchable(function () {
            $post = factory(Post::class)->create();

            $this->browse(function (Browser $browser) use ($post) {
                $browser->loginAs(User::find(1))
                        ->visit(new Pages\Create('comments'))
                        ->select('@commentable-type', 'posts')
                        ->pause(500)
                        ->searchAndSelectFirstRelation('commentable', 1)
                        ->type('@body', 'Test Comment')
                        ->create();

                $browser->assertPathIs('/nova/resources/comments/1');

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
        $this->setupLaravel();

        $post = factory(Post::class)->create();

        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('posts', $post->id))
                    ->waitFor('@comments-index-component', 5)
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

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function morph_to_field_should_honor_custom_labels()
    {
        $this->setupLaravel();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Create('comments'))
                    ->assertSee('User Post')
                    ->assertSee('User Video');

            $browser->blank();
        });
    }
}
