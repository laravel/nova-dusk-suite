<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\RoleFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Components\Modals\ConfirmActionModalComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class ActionModalAbandonmentTest extends DuskTestCase
{
    public function test_modal_shows_exit_warning_dialog_if_form_has_changes()
    {
        $this->browse(function (Browser $browser) {
            $role = RoleFactory::new()->create();

            $role->users()->attach(1);

            $browser->loginAs(1)
                ->visit(new Detail('roles', $role->id))
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->clickCheckboxForId(1)
                        ->selectAction('update-required-pivot-notes', function ($browser) {
                            $browser->elsewhere('', function ($browser) {
                                $browser->whenAvailable(new ConfirmActionModalComponent, function ($browser) {
                                    $browser->keys('@notes', 'Custom Notes', '{tab}');
                                })
                                    ->assertPresentModal()
                                    ->keys('', '{escape}')
                                    ->waitForDialog()
                                    ->assertDialogOpened('Do you really want to leave? You have unsaved changes.')
                                    ->acceptDialog()
                                    ->pause(100)
                                    ->assertMissingModal();
                            });
                        });
                });

            $browser->blank();
        });
    }

    public function test_it_doesnt_show_exit_warning_if_modal_has_changes_when_clicking_cancel()
    {
        $this->browse(function (Browser $browser) {
            $role = RoleFactory::new()->create();

            $role->users()->attach(1);

            $browser->loginAs(1)
                ->visit(new Detail('roles', $role->id))
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->clickCheckboxForId(1)
                        ->selectAction('update-required-pivot-notes', function ($browser) {
                            $browser->elsewhereWhenAvailable(new ConfirmActionModalComponent, function ($browser) {
                                $browser->type('@notes', 'Custom Notes')->cancel();
                            })
                                ->pause(100)
                                ->assertMissingModal();
                        });
                });

            $browser->blank();
        });
    }
}
