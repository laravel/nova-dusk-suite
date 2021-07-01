<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Testing\Browser\Pages\Index;
use Laravel\Nova\Testing\Browser\Pages\UserIndex;
use Laravel\Nova\Tests\DuskTestCase;

class ResourceFormAbandonmentTest extends DuskTestCase
{
    /** @test */
    public function it_can_show_dialog_if_resource_form_has_changes_on_navigating_to_different_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Index('videos'))
                    ->within(new IndexComponent('videos'), function ($browser) {
                        $browser->waitFor('@create-button')->click('@create-button');
                    })
                    ->on(new Create('videos'))
                    ->keys('@title', 'Hello World', '{tab}')
                    ->click('@users-resource-link')
                    ->assertDialogOpened('Do you really want to leave? You have unsaved changes.')
                    ->acceptDialog()
                    ->on(new UserIndex);

            $browser->blank();
        });
    }

    /** @test */
    public function it_can_show_dialog_if_resource_form_has_changes_on_clicking_cancel()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Index('videos'))
                    ->within(new IndexComponent('videos'), function ($browser) {
                        $browser->waitFor('@create-button')->click('@create-button');
                    })
                    ->on(new Create('videos'))
                    ->keys('@title', 'Hello World', '{tab}')
                    ->clickLink('Cancel')
                    ->assertDialogOpened('Do you really want to leave? You have unsaved changes.')
                    ->acceptDialog()
                    ->on(new Index('videos'));

            $browser->blank();
        });
    }

    /** @test */
    public function it_can_show_dialog_if_resource_form_has_changes_on_browser_back()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Index('videos'))
                    ->within(new IndexComponent('videos'), function ($browser) {
                        $browser->waitFor('@create-button')->click('@create-button');
                    })
                    ->on(new Create('videos'))
                    ->keys('@title', 'Hello World', '{tab}')
                    ->pause(500)
                    ->back()
                    ->assertDialogOpened('Do you really want to leave? You have unsaved changes.')
                    ->acceptDialog()
                    ->on(new Index('videos'));

            $browser->blank();
        });
    }
}
