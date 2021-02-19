<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\RoleFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
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
}
