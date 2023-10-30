<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\PostFactory;
use Database\Factories\RoleFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class DetailActionTest extends DuskTestCase
{
    /**
     * @test
     */
    public function can_run_actions_on_resource()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->waitForTextIn('h1', 'User Details: Taylor Otwell')
                ->runAction('mark-as-active')
                ->waitForText('The action was executed successfully.');

            $this->assertEquals(1, User::find(1)->active);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function cannot_run_actions_on_deleted_resource()
    {
        $role = RoleFactory::new()->create();
        $role->users()->attach(4);

        $this->browse(function (Browser $browser) use ($role) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 4))
                ->waitForTextIn('h1', 'User Details: Laravel Nova');

            $browser->within(new IndexComponent('roles'), function ($browser) use ($role) {
                $browser->waitForTable()
                    ->clickCheckboxForId(1);

                $role->users()->detach(4);

                $browser->runAction('update-pivot-notes', function ($browser) {
                    $browser->assertSee('Provide a description for notes.')
                        ->type('@notes', 'Custom Notes');
                });
            })->waitForText('Sorry! You are not authorized to perform this action.')
                ->assertSee('Sorry! You are not authorized to perform this action.');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function cannot_run_standalone_actions_on_deleted_resource()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 4))
                ->waitForTextIn('h1', 'User Details: Laravel Nova');

            User::where('id', '=', 4)->delete();

            $browser->runAction('standalone-task')
                ->waitForText('This resource no longer exists')
                ->assertSee('Action executed!')
                ->assertSee('This resource no longer exists');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function actions_can_be_cancelled_without_effect()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->waitForTextIn('h1', 'User Details: Taylor Otwell')
                ->cancelAction('mark-as-active');

            $this->assertEquals(0, User::find(1)->active);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function actions_on_all_matching_relations_should_be_scoped_to_the_relation()
    {
        $post = PostFactory::new()->create([
            'user_id' => 1,
        ]);

        $post2 = PostFactory::new()->create([
            'user_id' => 2,
        ]);

        $this->browse(function (Browser $browser) use ($post, $post2) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->waitForTextIn('h1', 'User Details: Taylor Otwell')
                ->within(new IndexComponent('posts'), function ($browser) {
                    $browser->waitForTable()
                        ->selectAllMatching()
                        ->runAction('mark-as-active');
                })->waitForText('The action was executed successfully.');

            $this->assertEquals(1, $post->fresh()->active);
            $this->assertEquals(0, $post2->fresh()->active);

            $browser->blank();
        });
    }
}
