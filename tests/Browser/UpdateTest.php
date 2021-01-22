<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\PostFactory;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Tests\DuskTestCase;

class UpdateTest extends DuskTestCase
{
    /**
     * @test
     */
    public function cant_view_update_page_if_not_authorized_to_update()
    {
        $post = PostFactory::new()->create();
        $post2 = PostFactory::new()->create();

        $user = User::find(1);
        $user->shouldBlockFrom('post.update.'.$post->id);

        $this->browse(function (Browser $browser) use ($user, $post, $post2) {
            $browser->loginAs($user)
                    ->visit(new Update('posts', $post->id))
                    ->assertPathIs('/nova/403');

            $browser->visit(new Update('posts', $post2->id))
                    ->assertPathIsNot('/nova/403');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function resource_can_be_updated()
    {
        $user = User::find(1);
        $user->name = 'Taylor';
        $user->save();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit(new Update('users', 1))
                    ->waitForTextIn('h1', 'Update User: 1', 25)
                    ->assertSee('E-mail address should be unique')
                    ->type('@name', 'Taylor Otwell upDATED')
                    ->type('@password', 'secret')
                    ->update();

            $user = User::find(1);

            $this->assertEquals('Taylor Otwell Updated', $user->name);
            $this->assertTrue(Hash::check('secret', $user->password));

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function validation_errors_are_displayed()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Update('users', 1))
                    ->waitForTextIn('h1', 'Update User: 1', 25)
                    ->type('@name', ' ')
                    ->update()
                    ->waitForText('There was a problem submitting the form.', 15)
                    ->assertSee('E-mail address should be unique')
                    ->assertSee('The Name field is required.');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function resource_can_be_updated_and_user_can_continue_editing()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Update('users', 1))
                    ->waitForTextIn('h1', 'Update User: 1', 25)
                    ->type('@name', 'Taylor Otwell Updated')
                    ->type('@password', 'secret')
                    ->assertSee('E-mail address should be unique')
                    ->updateAndContinueEditing();

            $user = User::find(1);

            $browser->assertPathIs('/nova/resources/users/'.$user->id.'/edit');

            $this->assertEquals('Taylor Otwell Updated', $user->name);
            $this->assertTrue(Hash::check('secret', $user->password));

            $browser->blank();
        });
    }
}
