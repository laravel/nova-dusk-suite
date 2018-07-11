<?php

namespace Tests\Browser;

use App\Post;
use App\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\IndexComponent;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class DetailTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function can_view_resource_attributes()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->assertSee('Taylor Otwell')
                    ->assertSee('taylor@laravel.com');
        });
    }

    /**
     * @test
     */
    public function can_run_actions_on_resource()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->runAction('mark-as-active');

            $this->assertEquals(1, User::find(1)->active);
        });
    }

    /**
     * @test
     */
    public function actions_can_be_cancelled_without_effect()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->cancelAction('mark-as-active');

            $this->assertEquals(0, User::find(1)->active);
        });
    }

    /**
     * @test
     */
    public function can_navigate_to_edit_page()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->click('@edit-resource-button')
                    ->pause(250)
                    ->assertPathIs('/nova/resources/users/1/edit');
        });
    }

    /**
     * @test
     */
    public function resource_can_be_deleted()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 3))
                    ->delete();

            $this->assertNull(User::where('id', 3)->first());
        });
    }

    /**
     * @test
     */
    public function relationships_can_be_searched()
    {
        $this->seed();

        $user = User::find(1);
        $user->posts()->save($post = factory(Post::class)->create());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->within(new IndexComponent('posts'), function ($browser) {
                        $browser->assertSeeResource(1)
                                ->searchFor('No Matching Posts')
                                ->assertDontSeeResource(1);
                    });
        });
    }
}
