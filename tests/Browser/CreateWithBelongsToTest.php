<?php

namespace Tests\Browser;

use App\Post;
use App\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\IndexComponent;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class CreateWithBelongsToTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function resource_can_be_created()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Create('posts'))
                    ->select('@user', 1)
                    ->type('@title', 'Test Post')
                    ->type('@body', 'Test Post Body')
                    ->create();

            $user = User::find(1);
            $post = $user->posts->first();
            $this->assertEquals('Test Post', $post->title);
            $this->assertEquals('Test Post Body', $post->body);
        });
    }

    /**
     * @test
     */
    public function parent_resource_should_be_locked_when_creating_via_parents_detail_page()
    {
        $this->seed();

        $user = User::find(1);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->within(new IndexComponent('posts'), function ($browser) {
                        $browser->click('@create-button');
                    })
                    ->on(new Pages\Create('posts'))
                    ->assertDisabled('@user')
                    ->type('@title', 'Test Post')
                    ->type('@body', 'Test Post Body')
                    ->create();

            $user = User::find(1);
            $post = $user->posts->first();
            $this->assertEquals('Test Post', $post->title);
            $this->assertEquals('Test Post Body', $post->body);
        });
    }
}
