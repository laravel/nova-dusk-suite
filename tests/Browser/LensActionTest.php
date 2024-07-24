<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Post;
use App\Models\User;
use Database\Factories\PostFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\ActionDropdownComponent;
use Laravel\Nova\Testing\Browser\Components\LensComponent;
use Laravel\Nova\Testing\Browser\Pages\Lens;
use Laravel\Nova\Tests\DuskTestCase;

class LensActionTest extends DuskTestCase
{
    public function test_can_run_actions_on_selected_resources()
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

    public function test_can_run_table_row_actions_on_selected_resources()
    {
        User::whereIn('id', [2, 3, 4])->update(['active' => true]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Lens('users', 'passthrough-lens'))
                ->within(new LensComponent('users', 'passthrough-lens'), function ($browser) {
                    $browser->waitForTable()
                        ->openControlSelectorById(1)
                        ->elsewhereWhenAvailable(new ActionDropdownComponent, function ($browser) {
                            $browser->waitFor('@1-preview-button')
                                ->assertMissing('@1-inline-actions');
                        })
                        ->closeCurrentDropdown()
                        ->openControlSelectorById(2)
                        ->elsewhereWhenAvailable(new ActionDropdownComponent, function ($browser) {
                            $browser->assertSee('Mark As Inactive');
                        })
                        ->closeCurrentDropdown()
                        ->runInlineAction(2, 'mark-as-inactive');
                })
                ->waitForText('The action was executed successfully.');

            $this->assertEquals([
                1 => false,
                2 => false,
                3 => true,
                4 => true,
            ], User::findMany([1, 2, 3, 4])->pluck('active', 'id')->all());

            $browser->blank();
        });
    }

    public function test_can_run_actions_on_all_matching_resources()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Lens('users', 'passthrough-lens'))
                ->within(new LensComponent('users', 'passthrough-lens'), function ($browser) {
                    $browser->waitForTable()
                        ->selectFilter('Select First', '2')
                        ->waitForTable();

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

    public function test_can_run_actions_on_selected_resources_via_search()
    {
        $this->browse(function (Browser $browser) {
            PostFactory::new()->create([
                'user_id' => 2,
            ]);
            PostFactory::new()->times(3)->create();

            $browser->loginAs(1)
                ->visit(new Lens('posts', 'post'))
                ->within(new LensComponent('posts', 'post'), function ($browser) {
                    $browser->waitForTable()
                        ->searchFor('James Brooks')
                        ->waitForTable();

                    $browser->selectAllMatching()
                        ->runAction('mark-as-active');
                })->waitForText('The action was executed successfully.');

            $this->assertEquals([
                1 => true,
                2 => false,
                3 => false,
                4 => false,
            ], Post::findMany([1, 2, 3, 4])->pluck('active', 'id')->all());

            $browser->blank();
        });
    }
}
