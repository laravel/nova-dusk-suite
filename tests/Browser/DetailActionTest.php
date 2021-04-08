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
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->waitForTextIn('h1', 'User Details: 1')
                    ->runAction('mark-as-active')
                    ->waitForText('The action ran successfully!');

            $this->assertEquals(1, User::find(1)->active);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function cannot_run_actions_on_deleted_resource()
    {
        $user = User::find(4);
        $role = RoleFactory::new()->create();
        $user->roles()->attach($role);

        $this->browse(function (Browser $browser) use ($user, $role) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 4))
                    ->waitForTextIn('h1', 'User Details: 4');

            $browser->within(new IndexComponent('roles'), function ($browser) use ($user, $role) {
                $browser->waitForTable()
                    ->clickCheckboxForId(1);

                $user->roles()->detach($role);

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
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 4))
                    ->waitForTextIn('h1', 'User Details: 4');

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
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->waitForTextIn('h1', 'User Details: 1')
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
        $user = User::find(1);
        $user->posts()->save($post = PostFactory::new()->create());

        $user2 = User::find(2);
        $user2->posts()->save($post2 = PostFactory::new()->create());

        $this->browse(function (Browser $browser) use ($post, $post2) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->waitForTextIn('h1', 'User Details: 1')
                    ->within(new IndexComponent('posts'), function ($browser) {
                        $browser->waitForTable()
                                ->selectAllMatching()
                                ->runAction('mark-as-active');
                    });

            $this->assertEquals(1, $post->fresh()->active);
            $this->assertEquals(0, $post2->fresh()->active);

            $browser->blank();
        });
    }
}
