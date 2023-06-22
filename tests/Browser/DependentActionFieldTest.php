<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\CaptainFactory;
use Database\Factories\PostFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Notifications\NovaNotification;
use Laravel\Nova\Testing\Browser\Components\ActionDropdownComponent;
use Laravel\Nova\Testing\Browser\Components\Controls\RelationSelectControlComponent;
use Laravel\Nova\Testing\Browser\Components\DetailComponent;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Components\Modals\ConfirmActionModalComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\Index;
use Laravel\Nova\Testing\Browser\Pages\UserIndex;
use Laravel\Nova\Tests\DuskTestCase;

/**
 * @covers \Laravel\Nova\Fields\Field::dependsOn()
 * @covers \Laravel\Nova\Http\Controllers\ActionController::sync()
 */
class DependentActionFieldTest extends DuskTestCase
{
    public function test_it_can_sync_dependent_fields()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex)
                ->within(new IndexComponent('users'), function (Browser $browser) {
                    $browser->clickCheckboxForId(1)
                        ->runAction('send-notification', function ($browser) {
                            $browser->assertSelected('@type', '')
                                ->assertDisabled('@message')
                                ->assertDisabled('@icon')
                                ->select('@type', 'success')
                                ->waitFor('@action_url')
                                ->assertVisible('textarea[dusk="message"]')
                                ->assertMissing('input[type="text"][dusk="message"]')
                                ->type('@action_url', 'https://nova.laravel.com/released/4.0.0')
                                ->typeSlowly('@message', 'Laravel Nova has been released')
                                ->type('@action_text', 'View Release')
                                ->select('@icon', 'download');
                        });
                })->waitForText('The action was executed successfully.');

            $this->assertDatabaseHas('nova_notifications', [
                'type' => NovaNotification::class,
                'notifiable_type' => User::class,
                'notifiable_id' => 1,
                'data' => json_encode([
                    'component' => 'message-notification',
                    'icon' => 'download',
                    'message' => 'Laravel Nova has been released',
                    'actionText' => 'View Release',
                    'actionUrl' => [
                        'url' => 'https://nova.laravel.com/released/4.0.0',
                        'remote' => true,
                    ],
                    'openInNewTab' => false,
                    'type' => 'success',
                    'iconClass' => 'text-green-500',
                ]),
                'read_at' => null,
            ]);

            $browser->within(new IndexComponent('users'), function (Browser $browser) {
                $browser->clickCheckboxForId(2)
                    ->runAction('send-notification', function ($browser) {
                        $browser->assertSelected('@type', '')
                            ->assertDisabled('@message')
                            ->assertDisabled('@icon')
                            ->select('@type', 'warning')
                            ->pause(1000)
                            ->assertMissing('textarea[dusk="message"]')
                            ->assertVisible('input[type="text"][dusk="message"]')
                            ->assertMissing('@action_url')
                            ->assertMissing('@action_text')
                            ->typeSlowly('@message', 'Please change your password')
                            ->select('@icon', 'exclamation-circle');
                    });
            })->waitForText('The action was executed successfully.');

            $this->assertDatabaseHas('nova_notifications', [
                'type' => NovaNotification::class,
                'notifiable_type' => User::class,
                'notifiable_id' => 2,
                'data' => json_encode([
                    'component' => 'message-notification',
                    'icon' => 'exclamation-circle',
                    'message' => 'Please change your password',
                    'actionText' => 'View',
                    'actionUrl' => null,
                    'openInNewTab' => false,
                    'type' => 'warning',
                    'iconClass' => 'text-yellow-500',
                ]),
                'read_at' => null,
            ]);

            $browser->blank();
        });
    }

    public function test_it_can_sync_dependent_belongs_to_field()
    {
        $this->browse(function (Browser $browser) {
            $post = PostFactory::new()->create();

            $browser->loginAs(1)
                ->visit(new Index('posts'))
                ->within(new IndexComponent('posts'), function (Browser $browser) use ($post) {
                    $browser->waitForTable()
                        ->clickCheckboxForId($post->id)
                        ->runAction('add-comment', function ($browser) {
                            $browser->type('@body', 'Hello world')
                                ->whenAvailable('#anonymous-default-boolean-field', function ($browser) {
                                    $browser->assertChecked('');
                                });
                        });
                })
                ->waitForText('The action was executed successfully.');

            $this->assertDatabaseHas('comments', [
                'commentable_type' => $post->getMorphClass(),
                'commentable_id' => $post->id,
                'user_id' => null,
                'body' => 'Hello world',
            ]);

            $browser->within(new IndexComponent('posts'), function (Browser $browser) use ($post) {
                $browser->clickCheckboxForId($post->id)
                    ->runAction('add-comment', function ($browser) {
                        $browser->type('@body', 'Hello world with userId')
                            ->assertMissing('@user')
                            ->whenAvailable('#anonymous-default-boolean-field', function ($browser) {
                                $browser->assertChecked('')->uncheck('');
                            })->whenAvailable(new RelationSelectControlComponent('users'), function ($browser) {
                                $browser->select('', 4);
                            });
                    });
            })->waitForText('The action was executed successfully.');

            $this->assertDatabaseHas('comments', [
                'commentable_type' => $post->getMorphClass(),
                'commentable_id' => $post->id,
                'user_id' => 4,
                'body' => 'Hello world with userId',
            ]);

            $browser->blank();
        });
    }

    public function test_it_can_sync_dependent_select_field()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex)
                ->within(new IndexComponent('users'), function (Browser $browser) {
                    $browser->waitForTable()
                        ->runInlineAction(4, 'create-user-profile', function ($browser) {
                            $browser->assertSelected('@timezone', 'UTC')
                                ->type('@github', 'crynobone')
                                ->assertSelected('@timezone', 'Asia/Kuala_Lumpur');
                        });
                })->waitForText('User Profile created');

            $browser->blank();
        });
    }

    public function test_it_provides_selected_resources_information()
    {
        $this->browse(function (Browser $browser) {
            $captains = CaptainFactory::new()->times(5)->create();

            $browser->loginAs(1)
                ->visit(new Index('captains'))
                ->within(new IndexComponent('captains'), function ($browser) use ($captains) {
                    $browser->waitForTable()
                        ->selectAllOnCurrentPage()
                        ->selectAction('track-selected-action', function ($browser) use ($captains) {
                            $browser->assertValue('@selected_resources', 'false - '.$captains->pluck('id')->reverse()->join(','))
                                ->click('input[type="checkbox"][id="toggle-default-boolean-field"]')
                                ->pause(1000)
                                ->assertValue('@selected_resources', 'true - '.$captains->pluck('id')->reverse()->join(','))
                                ->cancel();
                        })
                        ->unselectAllOnCurrentPage()
                        ->selectAllMatching()
                        ->selectAction('track-selected-action', function ($browser) {
                            $browser->assertValue('@selected_resources', 'false - all')
                                ->click('input[type="checkbox"][id="toggle-default-boolean-field"]')
                                ->pause(1000)
                                ->assertValue('@selected_resources', 'true - all')
                                ->cancel();
                        });
                })
                ->visit(new Detail('captains', $captains[0]->getKey()))
                ->within(new DetailComponent('captains', $captains[0]->getKey()), function ($browser) use ($captains) {
                    $browser->openControlSelector()
                        ->elsewhereWhenAvailable(new ActionDropdownComponent(), function ($browser) use ($captains) {
                            $browser->click("button[data-action-id='track-selected-action']")
                                ->elsewhereWhenAvailable(new ConfirmActionModalComponent(), function ($browser) use ($captains) {
                                    $browser->assertValue('@selected_resources', 'false - '.$captains[0]->getKey())
                                        ->click('input[type="checkbox"][id="toggle-default-boolean-field"]')
                                        ->pause(1000)
                                        ->assertValue('@selected_resources', 'true - '.$captains[0]->getKey())
                                        ->cancel();
                                });
                        });
                });
        });
    }
}
