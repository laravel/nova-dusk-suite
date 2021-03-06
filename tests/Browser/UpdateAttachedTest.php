<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\RoleFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\UpdateAttached;
use Laravel\Nova\Tests\DuskTestCase;

class UpdateAttachedTest extends DuskTestCase
{
    /**
     * @test
     */
    public function attached_resource_can_be_updated()
    {
        $user = User::find(1);
        $role = RoleFactory::new()->create();
        $user->roles()->attach($role, ['notes' => 'Test Notes']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->within(new IndexComponent('roles'), function ($browser) {
                        $browser->waitForTable()
                                ->click('@1-edit-attached-button');
                    })
                    ->on(new UpdateAttached('users', 1, 'roles', 1))
                    ->whenAvailable('@via-resource-field', function ($browser) {
                        $browser->assertSee('User')->assertSee('1');
                    })
                    ->assertDisabled('@attachable-select')
                    ->assertInputValue('@notes', 'Test Notes')
                    ->type('@notes', 'Test Notes Updated')
                    ->update();

            $this->assertEquals('Test Notes Updated', User::find(1)->roles->first()->pivot->notes);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function attached_resource_can_be_updated_and_can_continue_editing()
    {
        $user = User::find(1);
        $role = RoleFactory::new()->create();
        $user->roles()->attach($role, ['notes' => 'Test Notes']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->within(new IndexComponent('roles'), function ($browser) {
                        $browser->waitForTable()
                                ->click('@1-edit-attached-button');
                    })
                    ->on(new UpdateAttached('users', 1, 'roles', 1))
                    ->whenAvailable('@via-resource-field', function ($browser) {
                        $browser->assertSee('User')->assertSee('1');
                    })
                    ->type('@notes', 'Test Notes Updated')
                    ->updateAndContinueEditing();

            $browser->assertPathIs('/nova/resources/users/1/edit-attached/roles/1');

            $this->assertEquals('Test Notes Updated', User::find(1)->roles->first()->pivot->notes);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function validation_errors_are_displayed()
    {
        $user = User::find(1);
        $role = RoleFactory::new()->create();
        $user->roles()->attach($role, ['notes' => 'Test Notes']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->within(new IndexComponent('roles'), function ($browser) {
                        $browser->waitForTable()
                                ->click('@1-edit-attached-button');
                    })
                    ->on(new UpdateAttached('users', 1, 'roles', 1))
                    ->whenAvailable('@via-resource-field', function ($browser) {
                        $browser->assertSee('User')->assertSee('1');
                    })
                    ->type('@notes', str_repeat('A', 30))
                    ->update()
                    ->assertSee('The notes may not be greater than 20 characters.');

            $this->assertEquals('Test Notes', User::find(1)->roles->first()->pivot->notes);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function it_can_update_attached_duplicate_relations_pivot()
    {
        Carbon::setTestNow($now = Carbon::now());

        DB::table('book_purchases')->insert([
            ['user_id' => 1, 'book_id' => 4, 'type' => 'personal', 'price' => 34, 'purchased_at' => $now->toDatetimeString()],
            ['user_id' => 1, 'book_id' => 4, 'type' => 'personal', 'price' => 32, 'purchased_at' => $now->toDatetimeString()],
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->within(new IndexComponent('books', 'personalBooks'), function ($browser) {
                        $browser->waitForTable()
                            ->within('tr[data-pivot-id="2"]', function ($browser) {
                                $browser->click('@4-edit-attached-button');
                            });
                    })
                    ->on(new UpdateAttached('users', 1, 'books', 4))
                    ->waitFor('.content form', 25)
                    ->type('@price', '43')
                    ->update()
                    ->waitForText('The resource was updated!', 15)
                    ->within(new IndexComponent('books', 'personalBooks'), function ($browser) {
                        $browser->waitForTable()
                            ->within('tr[data-pivot-id="1"]', function ($browser) {
                                $browser->assertSee('$34.00');
                            })
                            ->within('tr[data-pivot-id="2"]', function ($browser) {
                                $browser->assertSee('$43.00');
                            });
                    });
        });
    }
}
