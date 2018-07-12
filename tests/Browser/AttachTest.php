<?php

namespace Tests\Browser;

use App\Role;
use App\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\IndexComponent;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AttachTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function resource_can_be_attached()
    {
        $this->seed();

        $role = factory(Role::class)->create();

        $this->browse(function (Browser $browser) use ($role) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->within(new IndexComponent('roles'), function ($browser) {
                        $browser->click('@attach-button');
                    })
                    ->pause(750)
                    ->select('@attachable-select', $role->id)
                    ->click('@attach-button')
                    ->pause(750);

            $this->assertEquals($role->id, User::find(1)->roles->first()->id);
        });
    }
}
