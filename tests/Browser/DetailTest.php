<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\UserFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\Replicate;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Testing\Browser\Pages\UserIndex;
use Laravel\Nova\Tests\DuskTestCase;

class DetailTest extends DuskTestCase
{
    /**
     * @test
     */
    public function can_view_resource_attributes()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->waitForTextIn('h1', 'User Details: 1')
                    ->assertSee('User Details: 1')
                    ->assertSee('Taylor Otwell')
                    ->assertSee('taylor@laravel.com');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_view_resource_as_big_int()
    {
        $user = UserFactory::new()->create([
            'id' => 9121018173229432287,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', $user->id))
                    ->waitForTextIn('h1', 'User Details: '.$user->id)
                    ->assertSee('User Details: '.$user->id)
                    ->assertSee($user->email);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_navigate_to_edit_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->edit()
                    ->on(new Update('users', 1))
                    ->assertSeeIn('h1', 'Update User');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_navigate_to_edit_page_using_shortcut()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->keys('', ['e'])
                    ->on(new Update('users', 1))
                    ->assertSeeIn('h1', 'Update User');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_navigate_to_replicate_resource_screen()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 2))
                    ->replicate()
                    ->on(new Replicate('users', 2))
                    ->assertSeeIn('h1', 'Create User')
                    ->assertInputValue('@name', 'Mohamed Said')
                    ->assertInputValue('@email', 'mohamed@laravel.com')
                    ->assertSee('Create & Add Another')
                    ->assertSee('Create User');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function cannot_navigate_to_replicate_resource_screen_when_blocked_via_policy()
    {
        $user = User::find(1);
        $user->shouldBlockFrom('user.replicate.4');

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit(new Detail('users', 4))
                    ->waitFor('@edit-resource-button')
                    ->openControlSelector()
                    ->assertNotPresent('@replicate-resource-button');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_navigate_to_different_detail_screen()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 2))
                    ->waitForTextIn('h1', 'User Details: 2')
                    ->assertSeeIn('@users-detail-component', 'Mohamed Said');

            $browser->script([
                'Nova.visit("/resources/users/3");',
            ]);

            $browser->on(new Detail('users', 3))
                    ->waitForTextIn('h1', 'User Details: 3')
                    ->assertSeeIn('@users-detail-component', 'David Hemphill');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function resource_can_be_deleted()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 3))
                    ->waitForTextIn('h1', 'User Details: 3')
                    ->delete()
                    ->waitForText('The user was deleted')
                    ->on(new UserIndex);

            $this->assertNull(User::where('id', 3)->first());

            $browser->blank();
        });
    }
}
