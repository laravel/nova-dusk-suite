<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\UserFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\FormComponent;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Tests\DuskTestCase;

/**
 * @covers \Laravel\Nova\Fields\BooleanGroup::dependsOn()
 */
class DependentBooleanGroupFieldTest extends DuskTestCase
{
    public function test_it_can_apply_field_dependencies_when_creating()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Create('users'))
                ->waitForTextIn('h1', 'Create User')
                ->assertMissing('input[type="checkbox"][name="create"]')
                ->assertPresent('input[type="checkbox"][name="read"]')
                ->assertMissing('input[type="checkbox"][name="update"]')
                ->assertMissing('input[type="checkbox"][name="delete"]')
                ->check('read')
                ->type('@name', 'Mior Muhammad Zaki')
                ->type('@email', 'mior@laravel.com')
                ->type('@password', 'password')
                ->pause(2000)
                ->assertPresent('input[type="checkbox"][name="create"]')
                ->assertPresent('input[type="checkbox"][name="read"]')
                ->assertPresent('input[type="checkbox"][name="update"]')
                ->assertPresent('input[type="checkbox"][name="delete"]')
                ->create()
                ->waitForText('The user was created!');

            $browser->blank();

            $user = User::latest()->first();

            $this->assertSame('mior@laravel.com', $user->email);
            $this->assertSame([
                'read' => true,
                'create' => false,
                'delete' => false,
                'update' => false,
            ], $user->permissions);
        });
    }

    public function test_it_can_apply_can_apply_field_dependencies_when_updating()
    {
        $this->browse(function (Browser $browser) {
            $user = UserFactory::new()->create([
                'email' => 'mior@laravel.com',
                'permissions' => [
                    'read' => true,
                    'create' => true,
                    'delete' => false,
                    'update' => false,
                ],
            ]);

            $browser->loginAs(1)
                ->visit(new Update('users', $user->id))
                ->within(new FormComponent(), function ($browser) {
                    $browser->type('@email', 'mior@laravel-nova.com')->pause(2000);
                })->update()
                ->waitForText('The user was updated!');

            $browser->blank();

            $user->refresh();

            $this->assertSame('mior@laravel-nova.com', $user->email);
            $this->assertSame([
                'read' => true,
            ], $user->permissions);
        });
    }
}
