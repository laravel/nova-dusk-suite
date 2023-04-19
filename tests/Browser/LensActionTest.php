<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\LensComponent;
use Laravel\Nova\Testing\Browser\Pages\Lens;
use Laravel\Nova\Tests\DuskTestCase;

class LensActionTest extends DuskTestCase
{
    /**
     * @test
     */
    public function can_run_actions_on_selected_resources()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Lens('users', 'passthrough-lens'))
                ->within(new LensComponent('users', 'passthrough-lens'), function ($browser) {
                    $browser->waitForTable()
                        ->clickCheckboxForId(3)
                        ->clickCheckboxForId(2)
                        ->runAction('mark-as-active');
                })->waitForText('The action was executed successfully.');

            $this->assertEquals([
                1 => false,
                2 => true,
                3 => true,
                4 => false,
            ], User::findMany([1, 2, 3, 4])->pluck('active', 'id')->all());

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_run_table_row_actions_on_selected_resources()
    {
        User::whereIn('id', [2, 3, 4])->update(['active' => true]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Lens('users', 'passthrough-lens'))
                ->within(new LensComponent('users', 'passthrough-lens'), function ($browser) {
                    $browser->waitForTable()
                        ->openControlSelectorById(1)
                        ->elsewhere('', function ($browser) {
                            $browser->waitFor('@1-preview-button')
                                ->assertMissing('@1-inline-actions');
                        })
                        ->openControlSelectorById(2)
                        ->elsewhereWhenAvailable('@2-inline-actions', function ($browser) {
                            $browser->assertSee('Mark As Inactive');
                        })
                        ->runInlineAction(2, 'mark-as-inactive');
                })->waitForText('The action was executed successfully.');

            $this->assertEquals([
                1 => false,
                2 => false,
                3 => true,
                4 => true,
            ], User::findMany([1, 2, 3, 4])->pluck('active', 'id')->all());

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_run_actions_on_all_matching_resources()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Lens('users', 'passthrough-lens'))
                ->within(new LensComponent('users', 'passthrough-lens'), function ($browser) {
                    $browser->waitForTable()
                        ->selectFilter('Select First', '2');

                    $browser->selectAllMatching()
                        ->runAction('mark-as-active');
                })->waitForText('The action was executed successfully.');

            $this->assertEquals([
                1 => false,
                2 => true,
                3 => false,
                4 => false,
            ], User::findMany([1, 2, 3, 4])->pluck('active', 'id')->all());

            $browser->blank();
        });
    }
}
