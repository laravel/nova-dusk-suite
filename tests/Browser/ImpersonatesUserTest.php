<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\UserFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Dashboard;
use Laravel\Nova\Testing\Browser\Pages\UserIndex;
use Laravel\Nova\Tests\DuskTestCase;

class ImpersonatesUserTest extends DuskTestCase
{
    /** @test */
    public function it_can_impersonate_another_user()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(2)
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->openControlSelectorById(1)
                                ->elsewhere('', function ($browser) {
                                    $browser->assertVisible('@1-replicate-button')
                                            ->assertMissing('@1-impersonate-button');
                                })
                                ->openControlSelectorById(2)
                                ->elsewhere('', function ($browser) {
                                    $browser->assertVisible('@2-replicate-button')
                                            ->assertMissing('@2-impersonate-button');
                                })
                                ->openControlSelectorById(3)
                                ->elsewhere('', function ($browser) {
                                    $browser->assertVisible('@3-replicate-button')
                                            ->assertVisible('@3-impersonate-button');
                                })
                                ->openControlSelectorById(4)
                                ->elsewhere('', function ($browser) {
                                    $browser->assertVisible('@4-replicate-button')
                                            ->assertVisible('@4-impersonate-button')
                                            ->clickAndWaitForReload('@4-impersonate-button')
                                            ->assertPathIs('/')
                                            ->assertAuthenticatedAs(User::find(4));
                                });
                    })
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->openControlSelectorById(1)
                                ->elsewhere('', function ($browser) {
                                    $browser->assertVisible('@1-replicate-button')
                                            ->assertMissing('@1-impersonate-button');
                                })
                                ->openControlSelectorById(2)
                                ->elsewhere('', function ($browser) {
                                    $browser->assertVisible('@2-replicate-button')
                                            ->assertMissing('@2-impersonate-button');
                                })
                                ->openControlSelectorById(3)
                                ->elsewhere('', function ($browser) {
                                    $browser->assertVisible('@3-replicate-button')
                                            ->assertMissing('@3-impersonate-button');
                                })
                                ->openControlSelectorById(4)
                                ->elsewhere('', function ($browser) {
                                    $browser->assertVisible('@4-replicate-button')
                                            ->assertMissing('@4-impersonate-button');
                                });
                    })
                    ->visit(new Dashboard())
                    ->press('Laravel Nova')
                    ->press('Stop Impersonating')
                    ->assertDialogOpened('Are you sure you want to stop impersonating?')
                    ->acceptDialog()
                    ->on(new Dashboard())
                    ->assertAuthenticatedAs(User::find(2));

            $browser->blank();
        });
    }

    /** @test */
    public function it_can_impersonate_another_user_with_different_password()
    {
        $this->browse(function (Browser $browser) {
            $user = UserFactory::new()->create([
                'password' => 'a-unique-password',
            ]);

            $browser->loginAs(2)
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) use ($user) {
                        $browser->openControlSelectorById($user->id)
                                ->elsewhere('', function ($browser) use ($user) {
                                    $browser->assertVisible("@{$user->id}-replicate-button")
                                            ->assertVisible("@{$user->id}-impersonate-button")
                                            ->clickAndWaitForReload("@{$user->id}-impersonate-button")
                                            ->assertPathIs('/')
                                            ->assertAuthenticatedAs($user);
                                });
                    })
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->openControlSelectorById(1)
                                ->elsewhere('', function ($browser) {
                                    $browser->assertVisible('@1-replicate-button')
                                            ->assertMissing('@1-impersonate-button');
                                })
                                ->openControlSelectorById(2)
                                ->elsewhere('', function ($browser) {
                                    $browser->assertVisible('@2-replicate-button')
                                            ->assertMissing('@2-impersonate-button');
                                })
                                ->openControlSelectorById(3)
                                ->elsewhere('', function ($browser) {
                                    $browser->assertVisible('@3-replicate-button')
                                            ->assertMissing('@3-impersonate-button');
                                })
                                ->openControlSelectorById(4)
                                ->elsewhere('', function ($browser) {
                                    $browser->assertVisible('@4-replicate-button')
                                            ->assertMissing('@4-impersonate-button');
                                })
                                ->openControlSelectorById(5)
                                ->elsewhere('', function ($browser) {
                                    $browser->assertVisible('@5-replicate-button')
                                            ->assertMissing('@5-impersonate-button');
                                });
                    })
                    ->visit(new Dashboard())
                    ->press($user->name)
                    ->press('Stop Impersonating')
                    ->assertDialogOpened('Are you sure you want to stop impersonating?')
                    ->acceptDialog()
                    ->on(new Dashboard())
                    ->assertAuthenticatedAs(User::find(2));

            $browser->blank();
        });
    }
}
