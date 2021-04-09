<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Post;
use App\Models\User;
use Database\Factories\PostFactory;
use Database\Factories\RoleFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\Index;
use Laravel\Nova\Testing\Browser\Pages\UserIndex;
use Laravel\Nova\Tests\DuskTestCase;

class ActionFieldTest extends DuskTestCase
{
    /**
     * @test
     */
    public function actions_can_be_instantly_dispatched()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->visit('/')->assertMissing('Nova');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function actions_can_receive_and_utilize_field_input()
    {
        $user = User::find(1);
        $role = RoleFactory::new()->create();
        $user->roles()->attach($role);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($user = User::find(1))
                    ->visit(new Detail('users', 1))
                    ->within(new IndexComponent('roles'), function ($browser) {
                        $browser->waitForTable(25)
                            ->clickCheckboxForId(1)
                            ->runAction('update-pivot-notes', function ($browser) {
                                $browser->assertSee('Provide a description for notes.')
                                        ->type('@notes', 'Custom Notes');
                            });
                    })->waitForText('The action ran successfully!', 25);

            $this->assertEquals('Custom Notes', $user->fresh()->roles->first()->pivot->notes);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function actions_modal_shouldnt_closed_when_user_using_shortcut()
    {
        $user = User::find(1);
        $role = RoleFactory::new()->create();
        $user->roles()->attach($role);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($user = User::find(1))
                    ->visit(new Detail('users', 1))
                    ->within(new IndexComponent('roles'), function ($browser) {
                        $browser->waitForTable(25)
                            ->assertScript('Nova.useShortcuts', true)
                            ->clickCheckboxForId(1)
                            ->waitFor('@action-select')
                            ->select('@action-select', 'update-pivot-notes')
                            ->pause(100)
                            ->click('@run-action-button');

                        $browser->elsewhere('', function ($browser) {
                            $browser->whenAvailable('.modal', function ($browser) {
                                $browser->assertScript('Nova.useShortcuts', false)
                                        ->assertSee('Provide a description for notes.');
                            })->keys('', ['e']);
                        });
                    })
                    ->assertPresent('.modal')
                    ->assertPathIs('/nova/resources/users/1');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function actions_can_be_validated()
    {
        $user = User::find(1);
        $role = RoleFactory::new()->create();
        $user->roles()->attach($role);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($user = User::find(1))
                    ->visit(new Detail('users', 1))
                    ->within(new IndexComponent('roles'), function ($browser) {
                        $browser->waitForTable(25)
                            ->clickCheckboxForId(1)
                            ->runAction('update-required-pivot-notes')
                            ->elsewhere('.modal', function ($browser) {
                                $browser->assertSee('The Notes field is required.');
                            });
                    });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function actions_cant_be_executed_when_not_authorized_to_run()
    {
        User::whereIn('id', [1])->update(['active' => true]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                            ->assertSeeIn('@1-row', 'Mark As Inactive')
                            ->assertDontSeeIn('@2-row', 'Mark As Inactive')
                            ->assertDontSeeIn('@3-row', 'Mark As Inactive')
                            ->runInlineAction(1, 'mark-as-inactive');
                    })->waitForText('Sorry! You are not authorized to perform this action.', 25);

            $this->assertEquals(1, User::find(1)->active);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function cannot_run_standalone_actions_on_deleted_resource()
    {
        PostFactory::new()->times(5)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Index('posts'))
                    ->within(new IndexComponent('posts'), function ($browser) {
                        $browser->waitForTable();

                        Post::query()->delete();

                        $browser->runAction('standalone-task', function ($browser) {
                            $browser->assertSee('Provide a description for notes.')
                                    ->type('@notes', 'Custom Notes');
                        });
                    })->waitForText('Action executed with [Custom Notes]')
                    ->assertSee('Action executed with [Custom Notes]');

            $browser->blank();
        });
    }
}
