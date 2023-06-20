<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\RoleFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\SidebarComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\UserIndex;
use Laravel\Nova\Tests\DuskTestCase;

class AttachResourceFormAbandonmentTest extends DuskTestCase
{
    /** @test */
    public function it_shows_exit_warning_if_resource_form_has_changes_when_navigating_to_different_page()
    {
        RoleFactory::new()->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->runAttachRelation('roles')
                ->whenAvailable('@via-resource-field', function ($browser) {
                    $browser->assertSee('User')->assertSee('Taylor Otwell');
                })
                ->keys('@notes', 'Test Notes', '{tab}')
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
        RoleFactory::new()->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->runAttachRelation('roles')
                ->whenAvailable('@via-resource-field', function ($browser) {
                    $browser->assertSee('User')->assertSee('Taylor Otwell');
                })
                ->keys('@notes', 'Test Notes', '{tab}')
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
        RoleFactory::new()->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->runAttachRelation('roles')
                ->whenAvailable('@via-resource-field', function ($browser) {
                    $browser->assertSee('User')->assertSee('Taylor Otwell');
                })
                ->keys('@notes', 'Test Notes', '{tab}')
                ->cancel()
                ->on(new Detail('users', 1));

            $browser->blank();
        });
    }
}
