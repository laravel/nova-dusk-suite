<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\RoleFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\UpdateAttached;
use Laravel\Nova\Testing\Browser\Pages\UserIndex;
use Laravel\Nova\Tests\DuskTestCase;

class UpdateAttachedResourceFormAbandonmentTest extends DuskTestCase
{
    /** @test */
    public function it_shows_exit_warning_if_resource_form_has_changes_when_navigating_to_different_page()
    {
        $user = User::find(1);
        $role = RoleFactory::new()->create();
        $user->roles()->attach($role, ['notes' => 'Test Notes']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit(new Detail('users', 1))
                    ->within(new IndexComponent('roles'), function ($browser) {
                        $browser->waitForTable()
                                ->click('@1-edit-attached-button');
                    })
                    ->on(new UpdateAttached('users', 1, 'roles', 1))
                    ->whenAvailable('@via-resource-field', function ($browser) {
                        $browser->assertSee('User')->assertSee('1');
                    })
                    ->keys('@notes', 'Test Notes Updated', '{tab}')
                    ->within('.sidebar-menu', function ($browser) {
                        $browser->clickLink('Users');
                    })
                    ->assertDialogOpened('Do you really want to leave? You have unsaved changes.')
                    ->acceptDialog()
                    ->on(new UserIndex)
                    ->waitForTextIn('h1', 'Users');

            $browser->blank();
        });
    }

    /** @test */
    public function it_shows_exit_warning_if_resource_form_has_changes_when_clicking_browser_back_button()
    {
        $user = User::find(1);
        $role = RoleFactory::new()->create();
        $user->roles()->attach($role, ['notes' => 'Test Notes']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit(new Detail('users', 1))
                    ->within(new IndexComponent('roles'), function ($browser) {
                        $browser->waitForTable()
                                ->click('@1-edit-attached-button');
                    })
                    ->on(new UpdateAttached('users', 1, 'roles', 1))
                    ->whenAvailable('@via-resource-field', function ($browser) {
                        $browser->assertSee('User')->assertSee('1');
                    })
                    ->keys('@notes', 'Test Notes Updated', '{tab}')
                    ->back()
                    ->pause(500)
                    ->assertDialogOpened('Do you really want to leave? You have unsaved changes.')
                    ->acceptDialog()
                    ->on(new Detail('users', 1));

            $browser->blank();
        });
    }

    /** @test */
    public function it_doesnt_show_exit_warning_if_resource_form_has_changes_when_clicking_cancel()
    {
        $user = User::find(1);
        $role = RoleFactory::new()->create();
        $user->roles()->attach($role, ['notes' => 'Test Notes']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit(new Detail('users', 1))
                    ->within(new IndexComponent('roles'), function ($browser) {
                        $browser->waitForTable()
                                ->click('@1-edit-attached-button');
                    })
                    ->on(new UpdateAttached('users', 1, 'roles', 1))
                    ->whenAvailable('@via-resource-field', function ($browser) {
                        $browser->assertSee('User')->assertSee('1');
                    })
                    ->keys('@notes', 'Test Notes Updated', '{tab}')
                    ->click('@cancel-update-attached-button')
                    ->on(new Detail('users', 1));

            $browser->blank();
        });
    }
}
