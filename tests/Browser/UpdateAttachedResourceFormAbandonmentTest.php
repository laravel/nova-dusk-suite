<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\RoleFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Components\SidebarComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\UpdateAttached;
use Laravel\Nova\Testing\Browser\Pages\UserIndex;
use Laravel\Nova\Tests\DuskTestCase;

class UpdateAttachedResourceFormAbandonmentTest extends DuskTestCase
{
    /** @test */
    public function it_shows_exit_warning_if_resource_form_has_changes_when_navigating_to_different_page()
    {
        $role = RoleFactory::new()->create();
        $role->users()->attach(1, ['notes' => 'Test Notes']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->within(new IndexComponent('roles'), function ($browser) {
                    $browser->waitForTable()
                        ->click('@1-edit-attached-button');
                })
                ->on(UpdateAttached::belongsToMany('users', 1, 'roles', 1))
                ->whenAvailable('@via-resource-field', function ($browser) {
                    $browser->assertSee('User')->assertSee('Taylor Otwell');
                })
                ->keys('@notes', 'Test Notes Updated', '{tab}')
                ->within(new SidebarComponent(), function ($browser) {
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
        $role = RoleFactory::new()->create();
        $role->users()->attach(1, ['notes' => 'Test Notes']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->within(new IndexComponent('roles'), function ($browser) {
                    $browser->waitForTable()
                        ->click('@1-edit-attached-button');
                })
                ->on(UpdateAttached::belongsToMany('users', 1, 'roles', 1))
                ->whenAvailable('@via-resource-field', function ($browser) {
                    $browser->assertSee('User')->assertSee('Taylor Otwell');
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
        $role = RoleFactory::new()->create();
        $role->users()->attach(1, ['notes' => 'Test Notes']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->within(new IndexComponent('roles'), function ($browser) {
                    $browser->waitForTable()
                        ->click('@1-edit-attached-button');
                })
                ->on(UpdateAttached::belongsToMany('users', 1, 'roles', 1))
                ->whenAvailable('@via-resource-field', function ($browser) {
                    $browser->assertSee('User')->assertSee('Taylor Otwell');
                })
                ->keys('@notes', 'Test Notes Updated', '{tab}')
                ->cancel()
                ->on(new Detail('users', 1));

            $browser->blank();
        });
    }
}
