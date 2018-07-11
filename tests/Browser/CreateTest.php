<?php

namespace Tests\Browser;

use App\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class CreateTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function resource_index_can_be_viewed()
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
}
