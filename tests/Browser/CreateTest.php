<?php

namespace Tests\Browser;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CreateTest extends DuskTestCase
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
                    ->visit(new Pages\Create('users'))
                    ->type('@name', 'Adam Wathan')
                    ->type('@email', 'adam@laravel.com')
                    ->type('@password', 'secret')
                    ->create();

            $user = User::orderBy('id', 'desc')->first();

            $browser->assertPathIs('/nova/resources/users/'.$user->id);

            $this->assertEquals('Adam Wathan', $user->name);
            $this->assertEquals('adam@laravel.com', $user->email);
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
                    ->visit(new Pages\Create('users'))
                    ->create()
                    ->assertSee('The name field is required.')
                    ->assertSee('The email field is required.')
                    ->assertSee('The password field is required.');
        });
    }

    /**
     * @test
     */
    public function resource_can_be_created_and_another_resource_can_be_added()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Create('users'))
                    ->type('@name', 'Adam Wathan')
                    ->type('@email', 'adam@laravel.com')
                    ->type('@password', 'secret')
                    ->createAndAddAnother();

            $user = User::orderBy('id', 'desc')->first();

            $browser->assertPathIs('/nova/resources/users/new');

            $this->assertEquals('Adam Wathan', $user->name);
            $this->assertEquals('adam@laravel.com', $user->email);
            $this->assertTrue(Hash::check('secret', $user->password));
        });
    }
}
