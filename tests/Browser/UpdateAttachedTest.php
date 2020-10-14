<?php

namespace Laravel\Nova\Tests\Browser;

use App\Role;
use App\User;
use Laravel\Dusk\Browser;
use Laravel\Nova\Tests\Browser\Components\IndexComponent;
use Laravel\Nova\Tests\DuskTestCase;

class UpdateAttachedTest extends DuskTestCase
{
    /**
     * @test
     */
    public function attached_resource_can_be_updated()
    {
        $this->setupLaravel();

        $user = User::find(1);
        $role = factory(Role::class)->create();
        $user->roles()->attach($role, ['notes' => 'Test Notes']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->waitFor('@roles-index-component', 5)
                    ->within(new IndexComponent('roles'), function ($browser) {
                        $browser->click('@1-edit-attached-button');
                    })
                    ->on(new Pages\UpdateAttached('users', 1, 'roles', 1))
                    ->assertDisabled('@attachable-select')
                    ->assertInputValue('@notes', 'Test Notes')
                    ->type('@notes', 'Test Notes Updated')
                    ->update();

            $this->assertEquals('Test Notes Updated', User::find(1)->roles->first()->pivot->notes);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function attached_resource_can_be_updated_and_can_continue_editing()
    {
        $this->setupLaravel();

        $user = User::find(1);
        $role = factory(Role::class)->create();
        $user->roles()->attach($role, ['notes' => 'Test Notes']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->waitFor('@roles-index-component', 5)
                    ->within(new IndexComponent('roles'), function ($browser) {
                        $browser->click('@1-edit-attached-button');
                    })
                    ->on(new Pages\UpdateAttached('users', 1, 'roles', 1))
                    ->type('@notes', 'Test Notes Updated')
                    ->updateAndContinueEditing();

            $browser->assertPathIs('/nova/resources/users/1/edit-attached/roles/1');

            $this->assertEquals('Test Notes Updated', User::find(1)->roles->first()->pivot->notes);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function validation_errors_are_displayed()
    {
        $this->setupLaravel();

        $user = User::find(1);
        $role = factory(Role::class)->create();
        $user->roles()->attach($role, ['notes' => 'Test Notes']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->waitFor('@roles-index-component', 5)
                    ->within(new IndexComponent('roles'), function ($browser) {
                        $browser->click('@1-edit-attached-button');
                    })
                    ->on(new Pages\UpdateAttached('users', 1, 'roles', 1))
                    ->type('@notes', str_repeat('A', 30))
                    ->update()
                    ->assertSee('The notes may not be greater than 20 characters.');

            $this->assertEquals('Test Notes', User::find(1)->roles->first()->pivot->notes);

            $browser->blank();
        });
    }
}
