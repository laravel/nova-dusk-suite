<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\PostFactory;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Laravel\Nova\Nova;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\Page;
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

        User::find(1)->shouldBlockFrom('post.update.'.$post->id);

        $this->browse(function (Browser $browser) use ($post, $post2) {
            $browser->loginAs(1)
                    ->visit(new Page("/resources/posts/{$post->id}/edit"))
                    ->assertForbidden();

            $browser->visit(new Update('posts', $post2->id))
                    ->assertPathIsNot(Nova::path().'/403');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function resource_can_be_updated()
    {
        User::whereKey(1)->update([
            'name' => 'Taylor',
            'settings' => ['pagination' => 'simple'],
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(2)
                    ->visit(new Update('users', 1))
                    ->waitForTextIn('h1', 'Update User: 1')
                    ->assertSee('E-mail address should be unique')
                    ->assertSelected('@settings.pagination', 'simple')
                    ->type('@name', 'Taylor Otwell upDATED')
                    ->type('@password', 'secret')
                    ->update()
                    ->waitForText('The user was updated!')
                    ->on(new Detail('users', 1));

            $user = User::find(1);

            $this->assertEquals('Taylor Otwell Updated', $user->name);
            $this->assertTrue(Hash::check('secret', $user->password));

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function test_user_isnt_logged_out_when_updating_their_own_resource()
    {
        User::whereKey(1)->update([
            'name' => 'Taylor',
            'settings' => ['pagination' => 'simple'],
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new Update('users', 1))
                    ->waitForTextIn('h1', 'Update User: 1')
                    ->assertSee('E-mail address should be unique')
                    ->assertSelected('@settings.pagination', 'simple')
                    ->type('@name', 'Taylor Otwell upDATED')
                    ->type('@password', 'secret')
                    ->update()
                    ->waitForText('The user was updated!')
                    ->on(new Detail('users', 1));

            $user = User::find(1);

            $this->assertEquals('Taylor Otwell Updated', $user->name);
            $this->assertTrue(Hash::check('secret', $user->password));

            $browser->logout()->blank();
        });
    }

    /**
     * @test
     */
    public function validation_errors_are_displayed()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new Update('users', 1))
                    ->waitForTextIn('h1', 'Update User: 1')
                    ->type('@name', ' ')
                    ->update()
                    ->waitForText('There was a problem submitting the form.')
                    ->assertSee('E-mail address should be unique')
                    ->assertSee('The Name field is required.')
                    ->click('@cancel-update-button');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function resource_can_be_updated_and_user_can_continue_editing()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(2)
                    ->visit(new Update('users', 1))
                    ->waitForTextIn('h1', 'Update User: 1')
                    ->type('@name', 'Taylor Otwell Updated')
                    ->type('@password', 'secret')
                    ->assertSee('E-mail address should be unique')
                    ->updateAndContinueEditing()
                    ->waitForText('The user was updated!')
                    ->on(new Update('users', 1));

            $user = User::find(1);

            $browser->on(new Update('users', $user->id));

            $this->assertEquals('Taylor Otwell Updated', $user->name);
            $this->assertTrue(Hash::check('secret', $user->password));

            $browser->blank();
        });
    }
}
