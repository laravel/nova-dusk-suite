<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Testing\Browser\Pages\UserIndex;
use Laravel\Nova\Tests\DuskTestCase;

class ResourceClickActionTest extends DuskTestCase
{
    public function test_it_handle_default_option()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex())
                ->within(new IndexComponent('users'), function (Browser $browser) {
                    $browser->click('@3-row');
                })
                ->waitForLocation((new Detail('users', 3))->url());

            $browser->blank();
        });
    }

    public function test_it_can_be_configured()
    {
        $this->browse(function (Browser $browser) {
            $fetchUser = function ($userId, $action) {
                $user = User::find($userId);
                $user->settings = ['clickAction' => $action];
                $user->save();

                return $user;
            };

            $browser->loginAs($fetchUser(1, 'detail'))
                ->visit(new UserIndex())
                ->within(new IndexComponent('users'), function (Browser $browser) {
                    $browser->click('@3-row');
                })
                ->waitForLocation((new Detail('users', 3))->url());

            $browser->loginAs($fetchUser(2, 'edit'))
                ->visit(new UserIndex())
                ->within(new IndexComponent('users'), function (Browser $browser) {
                    $browser->click('@3-row');
                })
                ->waitForLocation((new Update('users', 3))->url());

            $browser->loginAs($fetchUser(3, 'select'))
                ->visit(new UserIndex())
                ->within(new IndexComponent('users'), function (Browser $browser) {
                    $browser->click('@3-row')
                        ->assertChecked('[dusk="3-row"] input.checkbox');
                });

            $browser->loginAs($fetchUser(4, 'ignore'))
                ->visit(new UserIndex())
                ->within(new IndexComponent('users'), function (Browser $browser) {
                    $browser->click('@3-row')
                        ->assertNotChecked('[dusk="3-row"] input.checkbox');
                })->assertPathIs((new UserIndex())->url());

            $browser->blank();
        });
    }
}
