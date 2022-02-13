<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\UserFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class DetailTest extends DuskTestCase
{
    /**
     * @test
     */
    public function can_view_resource_attributes()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
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
            $browser->loginAs(1)
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
    public function can_navigate_to_different_screens()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1);

            // To Edit Resource screen
            $browser->visit(new Detail('users', 1))
                    ->edit()
                    ->waitForTextIn('h1', 'Update User')
                    ->assertPathIs('/nova/resources/users/1/edit');

            // To Edit Resource screen using shortcut
            $browser->visit(new Detail('users', 1))
                    ->keys('', ['e'])
                    ->waitForTextIn('h1', 'Update User')
                    ->assertPathIs('/nova/resources/users/1/edit');

            // To different Detail screen
            $browser->visit(new Detail('users', 2))
                    ->waitForTextIn('h1', 'User Details: 2')
                    ->assertSeeIn('@users-detail-component', 'Mohamed Said');

            $browser->script([
                'Nova.app.$router.push({ name: "detail", params: { resourceName: "users", resourceId: 3 }});',
            ]);

            $browser->waitForTextIn('h1', 'User Details: 3')
                    ->assertPathIs('/nova/resources/users/3')
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
                    ->waitForTextIn('h1', 'User Details: 3')
                    ->delete()
                    ->waitForText('The user was deleted')
                    ->assertPathIs('/nova/resources/users');

            $this->assertNull(User::where('id', 3)->first());

            $browser->blank();
        });
    }
}
