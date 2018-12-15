<?php

namespace Tests\Browser;

use App\Post;
use App\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UpdateTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function cant_view_update_page_if_not_authorized_to_update()
    {
        $this->seed();

        $post = factory(Post::class)->create();
        $post2 = factory(Post::class)->create();

        $user = User::find(1);
        $user->shouldBlockFrom('post.update.'.$post->id);

        $this->browse(function (Browser $browser) use ($post, $post2) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Update('posts', $post->id))
                    ->assertPathIs('/nova/403');

            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Update('posts', $post2->id))
                    ->assertPathIsNot('/nova/403');
        });
    }

    /**
     * @test
     */
    public function resource_can_be_updated()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Update('users', 1))
                    ->type('@name', 'Taylor Otwell Updated')
                    ->type('@password', 'secret')
                    ->update();

            $user = User::find(1);

            $browser->assertPathIs('/nova/resources/users/'.$user->id);

            $this->assertEquals('Taylor Otwell Updated', $user->name);
            $this->assertTrue(Hash::check('secret', $user->password));
        });
    }

    /**
     * @test
     */
    public function validation_errors_are_displayed()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Update('users', 1))
                    ->type('@name', ' ')
                    ->update()
                    ->assertSee('The name field is required.');
        });
    }

    /**
     * @test
     */
    public function resource_can_be_updated_and_user_can_continue_editing()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Update('users', 1))
                    ->type('@name', 'Taylor Otwell Updated')
                    ->type('@password', 'secret')
                    ->updateAndContinueEditing();

            $user = User::find(1);

            $browser->assertPathIs('/nova/resources/users/'.$user->id.'/edit');

            $this->assertEquals('Taylor Otwell Updated', $user->name);
            $this->assertTrue(Hash::check('secret', $user->password));
        });
    }
}
