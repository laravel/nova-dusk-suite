<?php

namespace Tests\Browser;

use App\Role;
use App\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Support\Facades\Hash;
use Tests\Browser\Components\IndexComponent;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UpdateAttachedTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function attached_resource_can_be_updated()
    {
        $this->seed();

        $user = User::find(1);
        $role = factory(Role::class)->create();
        $user->roles()->attach($role, ['notes' => 'Test Notes']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->within(new IndexComponent('roles'), function ($browser) {
                        $browser->click('@1-edit-attached-button');
                    })
                    ->pause(750)
                    ->assertDisabled('@attachable-select')
                    ->assertInputValue('@notes', 'Test Notes');
        });
    }
}
