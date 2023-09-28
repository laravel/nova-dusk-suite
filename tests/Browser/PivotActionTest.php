<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\RoleFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;
use PHPUnit\Framework\Assert as PHPUnit;

class PivotActionTest extends DuskTestCase
{
    /**
     * @test
     */
    public function pivot_tables_can_be_referred_to_using_a_custom_name()
    {
        RoleFactory::new()->create()->users()->attach(1);

        $this->browse(static function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->within(new IndexComponent('roles'), static function (Browser $browser) {
                    $browser->waitForTable()
                        ->clickCheckboxForId(1)
                        ->openActionSelector()
                        ->within('@action-select', static function (Browser $browser) {
                            $label = $browser->attribute('optgroup', 'label');

                            PHPUnit::assertEquals('Role Assignment', $label);
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

        $this->browse(static function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->within(new IndexComponent('roles'), static function (Browser $browser) {
                    $browser->waitForTable()
                        ->clickCheckboxForId(1)
                        ->runAction('update-pivot-notes');
                })->waitForText('The action was executed successfully.');

            $browser->blank();
        });

        $this->assertEquals('Pivot Action Notes', User::find(1)->roles()->first()->pivot->notes);
    }
}
