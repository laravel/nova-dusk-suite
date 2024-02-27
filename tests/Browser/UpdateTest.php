<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\PostFactory;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Laravel\Nova\Nova;
use Laravel\Nova\Testing\Browser\Components\BreadcrumbComponent;
use Laravel\Nova\Testing\Browser\Components\FormComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\Page;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Tests\DuskTestCase;

class UpdateTest extends DuskTestCase
{
    public function test_cant_view_update_page_if_not_authorized_to_update()
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

    public function test_validation_errors_are_displayed()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Update('users', 1))
                ->waitForTextIn('h1', 'Update User: Taylor Otwell')
                ->within(new FormComponent(), function ($browser) {
                    $browser->type('@name', ' ');
                })
                ->update()
                ->waitForText('There was a problem submitting the form.')
                ->assertSee('E-mail address should be unique')
                ->assertSee(__('validation.required', ['attribute' => 'Name']))
                ->cancel();

            $browser->blank();
        });
    }

    public function test_resource_can_be_updated()
    {
        User::whereKey(1)->update([
            'name' => 'Taylor',
            'settings' => ['pagination' => 'simple'],
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(2)
                ->visit(new Update('users', 1))
                ->waitForTextIn('h1', 'Update User: Taylor')
                ->within(new BreadcrumbComponent(), function ($browser) {
                    $browser->assertSeeLink('Users')
                        ->assertSeeLink('User Details: Taylor')
                        ->assertCurrentPageTitle('Update User');
                })
                ->within(new FormComponent(), function ($browser) {
                    $browser->assertSee('E-mail address should be unique')
                        ->assertSelected('@settings->pagination', 'simple')
                        ->type('@name', 'Taylor Otwell upDATED')
                        ->type('@password', 'secret');
                })
                ->update()
                ->waitForText('The user was updated!')
                ->on(new Detail('users', 1));

            $browser->logout()->blank();
        });

        $user = User::find(1);

        $this->assertEquals('Taylor Otwell Updated', $user->name);
        $this->assertTrue(Hash::check('secret', $user->password));

        RefreshDatabaseState::$migrated = false;
    }

    public function test_resource_can_be_updated_and_user_can_continue_editing()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(2)
                ->visit(new Update('users', 1))
                ->waitForTextIn('h1', 'Update User: Taylor Otwell')
                ->within(new FormComponent(), function ($browser) {
                    $browser->type('@name', 'Taylor Otwell Updated')
                        ->type('@password', 'secret')
                        ->assertSee('E-mail address should be unique');
                })
                ->updateAndContinueEditing()
                ->waitForText('The user was updated!')
                ->on(new Update('users', 1));

            $browser->logout()->blank();
        });

        $user = User::find(1);

        $this->assertEquals('Taylor Otwell Updated', $user->name);
        $this->assertTrue(Hash::check('secret', $user->password));

        RefreshDatabaseState::$migrated = false;
    }

    public function test_resource_can_be_updated_using_enter_key_and_redirected_to_detail_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(2)
                ->visit(new Update('users', 1))
                ->waitForTextIn('h1', 'Update User: Taylor Otwell')
                ->within(new FormComponent(), function ($browser) {
                    $browser->type('@password', 'secret')
                        ->type('@name', 'Taylor Otwell Updated')
                        ->keys('@name', '{enter}');
                })
                ->waitForText('The user was updated!')
                ->on(new Detail('users', 1));

            $browser->logout()->blank();
        });

        $user = User::find(1);

        $this->assertEquals('Taylor Otwell Updated', $user->name);
        $this->assertTrue(Hash::check('secret', $user->password));

        RefreshDatabaseState::$migrated = false;
    }

    public function test_user_isnt_logged_out_when_updating_their_own_resource()
    {
        User::whereKey(1)->update([
            'name' => 'Taylor',
            'settings' => ['pagination' => 'simple'],
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Update('users', 1))
                ->waitForTextIn('h1', 'Update User: Taylor')
                ->within(new FormComponent(), function ($browser) {
                    $browser->assertSee('E-mail address should be unique')
                        ->assertSelected('@settings->pagination', 'simple')
                        ->type('@name', 'Taylor Otwell upDATED')
                        ->type('@password', 'secret');
                })
                ->update()
                ->waitForText('The user was updated!')
                ->on(new Detail('users', 1));

            $browser->logout()->blank();
        });

        $user = User::find(1);

        $this->assertEquals('Taylor Otwell Updated', $user->name);
        $this->assertTrue(Hash::check('secret', $user->password));

        RefreshDatabaseState::$migrated = false;
    }
}
