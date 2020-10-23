<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Tests\DuskTestCase;

class CreateTest extends DuskTestCase
{
    /**
     * @test
     */
    public function resource_can_be_created()
    {
        $this->setupLaravel();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Create('users'))
                    ->type('@name', 'Adam Wathan')
                    ->type('@email', 'adam@laravel.com')
                    ->type('@password', 'secret')
                    ->create();

            $user = User::orderBy('id', 'desc')->first();

            $browser->assertPathIs('/nova/resources/users/'.$user->id);

            $this->assertEquals('Adam Wathan', $user->name);
            $this->assertEquals('adam@laravel.com', $user->email);
            $this->assertTrue(Hash::check('secret', $user->password));

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function validation_errors_are_displayed()
    {
        $this->setupLaravel();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Create('users'))
                    ->create()
                    ->assertSee('The Name field is required.')
                    ->assertSee('The Email field is required.')
                    ->assertSee('The Password field is required.');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function resource_can_be_created_and_another_resource_can_be_added()
    {
        $this->setupLaravel();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Create('users'))
                    ->type('@name', 'Adam Wathan')
                    ->type('@email', 'adam@laravel.com')
                    ->type('@password', 'secret')
                    ->createAndAddAnother();

            $user = User::orderBy('id', 'desc')->first();

            $browser->assertPathIs('/nova/resources/users/new');

            $this->assertEquals('Adam Wathan', $user->name);
            $this->assertEquals('adam@laravel.com', $user->email);
            $this->assertTrue(Hash::check('secret', $user->password));

            $browser->blank();
        });
    }
}
