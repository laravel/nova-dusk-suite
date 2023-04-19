<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\RoleFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class PivotActionTest extends DuskTestCase
{
    /**
     * @test
     */
    public function pivot_tables_can_be_referred_to_using_a_custom_name()
    {
        RoleFactory::new()->create()->users()->attach(1);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->within(new IndexComponent('roles'), function ($browser) {
                    $browser->waitForTable()
                        ->clickCheckboxForId(1)
                        ->openActionSelector()
                        ->within('@action-select', function ($browser) {
                            $label = $browser->attribute('optgroup', 'label');
                            $this->assertEquals('Role Assignment', $label);
                        });
                });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function actions_can_be_executed_against_pivot_rows()
    {
        RoleFactory::new()->create()->users()->attach(1);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->within(new IndexComponent('roles'), function ($browser) {
                    $browser->waitForTable()
                        ->clickCheckboxForId(1)
                        ->runAction('update-pivot-notes');
                })->waitForText('The action was executed successfully.');

            $this->assertEquals('Pivot Action Notes', User::find(1)->roles()->first()->pivot->notes);

            $browser->blank();
        });
    }
}
