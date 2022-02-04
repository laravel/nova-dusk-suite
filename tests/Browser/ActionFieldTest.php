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

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit(new Detail('users', 1))
                    ->within(new IndexComponent('roles'), function ($browser) {
                        $browser->waitForTable()
                            ->clickCheckboxForId(1)
                            ->runAction('update-pivot-notes', function ($browser) {
                                $browser->assertSee('Provide a description for notes.')
                                        ->type('@notes', 'Custom Notes');
                            });
                    })->waitForText('The action ran successfully!');

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

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit(new Detail('users', 1))
                    ->within(new IndexComponent('roles'), function ($browser) {
                        $browser->waitForTable()
                            ->assertScript('Nova.useShortcuts', true)
                            ->clickCheckboxForId(1)
                            ->selectAction('update-pivot-notes', function ($browser) {
                                $browser->elsewhere('', function ($browser) {
                                    $browser->whenAvailable('.modal[data-modal-open=true]', function ($browser) {
                                        $browser->assertScript('Nova.useShortcuts', false)
                                                ->assertSee('Provide a description for notes.');
                                    })->keys('', ['e']);
                                });
                            });
                    })
                    ->assertPresent('.modal[data-modal-open=true]');

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

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit(new Detail('users', 1))
                    ->within(new IndexComponent('roles'), function ($browser) {
                        $browser->waitForTable()
                            ->clickCheckboxForId(1)
                            ->runAction('update-required-pivot-notes')
                            ->elsewhere('.modal[data-modal-open=true]', function ($browser) {
                                $browser->assertSee('The Notes field is required.');
                            });
                    });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function actions_can_be_toggle_between_similar_fields()
    {
        $user = User::find(1);
        $role = RoleFactory::new()->create();
        $user->roles()->attach($role);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit(new Detail('users', 1))
                    ->within(new IndexComponent('roles'), function ($browser) {
                        $browser->waitForTable()
                            ->clickCheckboxForId(1)
                            ->selectAction('update-pivot-notes', function ($browser) {
                                $browser->assertSee('Provide a description for notes.')
                                    ->type('@notes', 'Custom Notes')
                                    ->click('[dusk="cancel-action-button"]')
                                    ->pause(250);
                            })
                            ->runAction('update-required-pivot-notes', function ($browser) {
                                $browser->type('@notes', 'Custom Notes Updated');
                            });
                    })->waitForText('The action ran successfully!');

            $this->assertEquals('Custom Notes Updated', $user->fresh()->roles->first()->pivot->notes);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function actions_cant_be_executed_when_not_authorized_to_run()
    {
        User::whereIn('id', [1, 2])->update(['active' => true]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($user = User::find(2))
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable()
                            ->assertDontSeeIn('@1-row', 'Mark As Inactive')
                            ->assertSeeIn('@2-row', 'Mark As Inactive');
                    });

            $this->assertEquals(1, $user->fresh()->active);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function actions_cant_be_closed_via_backdrop_when_form_has_been_updated()
    {
        $user = User::find(1);
        $role = RoleFactory::new()->create();
        $user->roles()->attach($role);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit(new Detail('users', $user->id))
                    ->within(new IndexComponent('roles'), function ($browser) {
                        $browser->waitForTable()
                            ->clickCheckboxForId(1)
                            ->whenAvailable('@action-select', function ($browser) {
                                $browser->select('', 'update-required-pivot-notes')
                                        ->pause(100);
                            });

                        $browser->elsewhere('', function ($browser) {
                            $browser->whenAvailable('.modal[data-modal-open=true]', function ($browser) {
                                $browser->type('@notes', 'Custom Notes')
                                        ->click('@modal-backdrop')
                                        ->assertDialogOpened('Do you really want to leave? You have unsaved changes.')
                                        ->dismissDialog();
                            })
                            ->assertPresent('.modal[data-modal-open=true]')
                            ->keys('', '{escape}')
                            ->assertDialogOpened('Do you really want to leave? You have unsaved changes.')
                            ->acceptDialog()
                            ->pause(100)
                            ->assertMissing('.modal[data-modal-open=true]');
                        });
                    });

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
