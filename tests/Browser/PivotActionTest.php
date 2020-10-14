<?php

namespace Laravel\Nova\Tests\Browser;

use App\Role;
use App\User;
use Laravel\Dusk\Browser;
use Laravel\Nova\Tests\Browser\Components\IndexComponent;
use Laravel\Nova\Tests\DuskTestCase;

class PivotActionTest extends DuskTestCase
{
    /**
     * @test
     */
    public function pivot_tables_can_be_referred_to_using_a_custom_name()
    {
        $this->setupLaravel();

        $user = User::find(1);
        $role = factory(Role::class)->create();
        $user->roles()->attach($role);

        $this->browse(function (Browser $browser) use ($user, $role) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->pause(1500)
                    ->within(new IndexComponent('roles'), function ($browser) {
                        $browser->clickCheckboxForId(1)
                                ->openActionSelector()
                                ->within('@action-select', function ($browser) {
                                    $label = $browser->attribute('optgroup.pivot-option-group', 'label');
                                    $this->assertEquals('Role Assignment', $label);
                                });
                    });
        });
    }

    /**
     * @test
     */
    public function actions_can_be_executed_against_pivot_rows()
    {
        $this->setupLaravel();

        $user = User::find(1);
        $role = factory(Role::class)->create();
        $user->roles()->attach($role);

        $this->browse(function (Browser $browser) use ($user, $role) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->pause(1500)
                    ->within(new IndexComponent('roles'), function ($browser) {
                        $browser->clickCheckboxForId(1)
                                ->runAction('update-pivot-notes');
                    });

            $this->assertEquals('Pivot Action Notes', $user->fresh()->roles()->first()->pivot->notes);

            $browser->blank();
        });
    }
}
