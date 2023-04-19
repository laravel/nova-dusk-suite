<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Post;
use App\Models\User;
use Database\Factories\PostFactory;
use Database\Factories\UserFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Index;
use Laravel\Nova\Testing\Browser\Pages\UserIndex;
use Laravel\Nova\Tests\DuskTestCase;

class IndexActionTest extends DuskTestCase
{
    public function test_can_run_actions_on_selected_resources()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
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

    public function test_cannot_run_actions_on_deleted_resources()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->clickCheckboxForId(3);

                    User::where('id', '=', 3)->delete();

                    $browser->runAction('mark-as-active');
                })->waitForText('Sorry! You are not authorized to perform this action.')
                ->assertSee('Sorry! You are not authorized to perform this action.');

            $browser->blank();
        });
    }

    public function test_cannot_run_standalone_actions_on_deleted_resource()
    {
        PostFactory::new()->times(5)->create(['user_id' => 2]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Index('posts'))
                ->within(new IndexComponent('posts'), function ($browser) {
                    $browser->waitForTable();

                    Post::query()->delete();

                    $browser->runAction('standalone-task');
                })->waitForText('Action executed!')
                ->assertSee('Action executed!');

            $browser->blank();
        });
    }

    public function test_can_run_actions_on_all_matching_resources()
    {
        UserFactory::new()->times(300)->create();

        $this->assertEquals(304, User::where('active', '=', 0)->count());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->selectAllMatching()
                        ->runAction('mark-as-active');
                })->waitForText('The action was executed successfully.');

            $this->assertEquals(0, User::where('active', '=', 0)->count());

            $browser->blank();
        });
    }

    public function test_can_run_table_row_actions_on_selected_resources()
    {
        User::whereIn('id', [2, 3, 4])->update(['active' => true]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
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
}
