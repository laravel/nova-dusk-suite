<?php

namespace Tests\Browser;

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
    public function resource_can_be_updated()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Update('users', 1))
                    ->type('@name', 'Taylor Otwell Updated')
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
    public function resource_can_be_updated_and_user_can_continue_editing()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Update('users', 1))
                    ->type('@name', 'Taylor Otwell Updated')
                    ->updateAndContinueEditing();

            $user = User::find(1);

            $browser->assertPathIs('/nova/resources/users/'.$user->id.'/edit');

            $this->assertEquals('Taylor Otwell Updated', $user->name);
            $this->assertTrue(Hash::check('secret', $user->password));
        });
    }
}
