<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Attach;
use Laravel\Nova\Tests\DuskTestCase;

class InlineCreateResourceModalAbandonmentTest extends DuskTestCase
{
    /**
     * @test
     */
    public function it_shows_exit_warning_dialog_if_modal_has_changes_when_pressing_escape()
    {
        $this->whileInlineCreate(function () {
            $this->browse(function (Browser $browser) {
                $browser->loginAs($user = User::find(1))
                    ->visit(new Attach('users', $user->getKey(), 'roles'))
                    ->showInlineCreate('roles', function ($browser) {
                        $browser->waitForText('Create Role')
                            ->keys('@name', 'Manager', '{tab}')
                            ->keys('', '{escape}');
                    })
                    ->assertDialogOpened('Do you really want to leave? You have unsaved changes.')
                    ->acceptDialog()
                    ->pause(100)
                    ->assertMissing('.modal[data-modal-open=true]')
                    ->on(new Attach('users', $user->getKey(), 'roles'));

                $browser->blank();
            });
        });
    }

    /**
     * @test
     */
    public function it_doesnt_show_exit_warning_when_clicking_cancel()
    {
        $this->whileInlineCreate(function () {
            $this->browse(function (Browser $browser) {
                $browser->loginAs($user = User::find(1))
                    ->visit(new Attach('users', $user->getKey(), 'roles'))
                    ->showInlineCreate('roles', function ($browser) {
                        $browser->waitForText('Create Role')
                            ->keys('@name', 'Manager', '{tab}')
                            ->click('@cancel-create-button');
                    })
                    ->pause(100)
                    ->assertMissing('.modal[data-modal-open=true]')
                    ->on(new Attach('users', $user->getKey(), 'roles'));

                $browser->blank();
            });
        });
    }
}
