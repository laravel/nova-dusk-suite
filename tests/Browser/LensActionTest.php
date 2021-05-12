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
            $browser->loginAs(User::find(1))
                    ->visit(new Lens('users', 'passthrough-lens'))
                    ->within(new LensComponent('users', 'passthrough-lens'), function ($browser) {
                        $browser->waitForTable(25)
                            ->clickCheckboxForId(3)
                            ->clickCheckboxForId(2)
                            ->runAction('mark-as-active');
                    });

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
            $browser->loginAs(User::find(1))
                    ->visit(new Lens('users', 'passthrough-lens'))
                    ->within(new LensComponent('users', 'passthrough-lens'), function ($browser) {
                        $browser->waitForTable(25)
                            ->assertDontSeeIn('@1-row', 'Mark As Inactive')
                            ->assertSeeIn('@2-row', 'Mark As Inactive')
                            ->runInlineAction(2, 'mark-as-inactive');
                    });

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
            $browser->loginAs(User::find(1))
                    ->visit(new Lens('users', 'passthrough-lens'))
                    ->within(new LensComponent('users', 'passthrough-lens'), function ($browser) {
                        $browser->waitForTable()
                                ->applyFilter('Select First', '2');

                        $browser->selectAllMatching()
                                ->runAction('mark-as-active');
                    });

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
