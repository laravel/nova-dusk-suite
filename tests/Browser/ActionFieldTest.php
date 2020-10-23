<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\RoleFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class ActionFieldTest extends DuskTestCase
{
    /**
     * @test
     */
    public function actions_can_be_instantly_dispatched()
    {
        $this->setupLaravel();

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
        $this->setupLaravel();

        $user = User::find(1);
        $role = RoleFactory::new()->create();
        $user->roles()->attach($role);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($user = User::find(1))
                    ->visit(new Detail('users', 1))
                    ->waitFor('@roles-index-component', 10)
                    ->within(new IndexComponent('roles'), function ($browser) {
                        $browser->clickCheckboxForId(1)
                            ->runAction('update-pivot-notes', function ($browser) {
                                $browser->type('@notes', 'Custom Notes');
                            });
                    });

            $this->assertEquals('Custom Notes', $user->fresh()->roles->first()->pivot->notes);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function actions_can_be_validated()
    {
        $this->setupLaravel();

        $user = User::find(1);
        $role = RoleFactory::new()->create();
        $user->roles()->attach($role);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($user = User::find(1))
                    ->visit(new Detail('users', 1))
                    ->waitFor('@roles-index-component', 10)
                    ->within(new IndexComponent('roles'), function ($browser) {
                        $browser->clickCheckboxForId(1)
                            ->runAction('update-required-pivot-notes')
                            ->elsewhere('.modal', function ($browser) {
                                $browser->assertSee('The Notes field is required.');
                            });
                    });

            $browser->blank();
        });
    }
}
