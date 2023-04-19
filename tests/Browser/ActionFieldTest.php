<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Post;
use App\Models\User;
use Database\Factories\PostFactory;
use Database\Factories\RoleFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Components\Modals\ConfirmActionModalComponent;
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
            $browser->loginAs(1)
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
        RoleFactory::new()->create()->users()->attach(1);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->within(new IndexComponent('roles'), function ($browser) {
                    $browser->waitForTable()
                        ->clickCheckboxForId(1)
                        ->runAction('update-pivot-notes', function ($browser) {
                            $browser->assertSee('Provide a description for notes.')
                                ->type('@notes', 'Custom Notes');
                        });
                })->waitForText('The action was executed successfully.');

            $this->assertEquals('Custom Notes', User::with('roles')->find(1)->roles->first()->pivot->notes);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function actions_modal_shouldnt_closed_when_user_using_shortcut()
    {
        RoleFactory::new()->create()->users()->attach(1);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->within(new IndexComponent('roles'), function ($browser) {
                    $browser->waitForTable()
                        ->assertScript('Nova.useShortcuts', true)
                        ->clickCheckboxForId(1)
                        ->selectAction('update-pivot-notes', function ($browser) {
                            $browser->elsewhere('', function ($browser) {
                                $browser->whenAvailable(new ConfirmActionModalComponent(), function ($browser) {
                                    $browser->assertScript('Nova.useShortcuts', false)
                                        ->assertSee('Provide a description for notes.');
                                })->keys('', ['e']);
                            });
                        });
                })
                ->assertPresentModal();

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function actions_can_be_validated()
    {
        RoleFactory::new()->create()->users()->attach(1);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->within(new IndexComponent('roles'), function ($browser) {
                    $browser->waitForTable()
                        ->clickCheckboxForId(1)
                        ->runAction('update-required-pivot-notes')
                        ->elsewhere(new ConfirmActionModalComponent(), function ($browser) {
                            $browser->assertSee(__('validation.required', ['attribute' => 'Notes']));
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
        RoleFactory::new()->create()->users()->attach(1);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
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
                })->waitForText('The action was executed successfully.');

            $this->assertEquals('Custom Notes Updated', User::with('roles')->find(1)->roles->first()->pivot->notes);

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
            $browser->loginAs(2)
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
                        });
                });

            $this->assertEquals(1, User::find(2)->active);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function cannot_run_standalone_actions_on_deleted_resource()
    {
        PostFactory::new()->times(5)->create(['user_id' => 1]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
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
