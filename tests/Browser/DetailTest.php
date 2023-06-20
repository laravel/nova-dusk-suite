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
        User::whereKey(1)->update([
            'settings' => ['pagination' => 'simple'],
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->waitForTextIn('h1', 'User Details: Taylor Otwell')
                ->within('@users-detail-component', function ($browser) {
                    $browser->assertSee('User Details: Taylor Otwell')
                        ->assertSeeIn('@name', 'Taylor Otwell')
                        ->assertSeeIn('@email', 'taylor@laravel.com')
                        ->assertSeeIn('@settings->pagination', 'Simple');
                });

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
            $browser->loginAs(1)
                ->visit(new Detail('users', $user->id))
                ->waitForTextIn('h1', 'User Details: '.$user->name)
                ->within('@users-detail-component', function ($browser) use ($user) {
                    $browser->assertSee('User Details: '.$user->name)
                        ->assertSeeIn('@id', $user->id)
                        ->assertSeeIn('@email', $user->email);
                });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_navigate_to_different_screens()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1);

            // To Edit Resource screen
            $browser->visit(new Detail('users', 1))
                ->edit()
                ->on(new Update('users', 1))
                ->assertSeeIn('h1', 'Update User');

            // To Edit Resource screen using shortcut
            $browser->visit(new Detail('users', 1))
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
            $browser->loginAs(1)
                ->visit(new Detail('users', 2))
                ->replicate()
                ->on(new Replicate('users', 2))
                ->assertSeeIn('h1', 'Create User')
                ->assertInputValue('@name', 'James Brooks')
                ->assertInputValue('@email', 'james@laravel.com')
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
        User::find(1)->shouldBlockFrom('user.replicate.4');

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 4))
                ->waitFor('@edit-resource-button')
                ->openControlSelector()
                ->assertNotPresent('@replicate-resource-button');

            // To different Detail screen
            $browser->visit(new Detail('users', 2))
                ->waitForTextIn('h1', 'User Details: James Brooks')
                ->assertSeeIn('@users-detail-component', 'James Brooks');

            $browser->script([
                'Nova.visit("/resources/users/3");',
            ]);

            $browser->on(new Detail('users', 3))
                ->waitForTextIn('h1', 'User Details: David Hemphill')
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
            $browser->loginAs(1)
                ->visit(new Detail('users', 3))
                ->waitForTextIn('h1', 'User Details: David Hemphill')
                ->delete()
                ->waitForText('The user was deleted')
                ->on(new UserIndex);

            $this->assertNull(User::where('id', 3)->first());

            $browser->blank();
        });
    }
}
