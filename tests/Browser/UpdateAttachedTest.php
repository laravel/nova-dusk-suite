<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\RoleFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\BreadcrumbComponent;
use Laravel\Nova\Testing\Browser\Components\FormComponent;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\UpdateAttached;
use Laravel\Nova\Tests\DuskTestCase;

class UpdateAttachedTest extends DuskTestCase
{
    public function test_attached_resource_can_be_updated()
    {
        $role = RoleFactory::new()->create();
        $role->users()->attach(1, ['notes' => 'Test Notes']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->within(new IndexComponent('roles'), function ($browser) {
                    $browser->waitForTable()
                        ->click('@1-edit-attached-button');
                })
                ->on(UpdateAttached::belongsToMany('users', 1, 'roles', 1))
                ->within(new BreadcrumbComponent(), function ($browser) {
                    $browser->assertSeeLink('Users')
                        ->assertSeeLink('User Details: Taylor Otwell')
                        ->assertCurrentPageTitle('Update attached Role: 1');
                })
                ->within(new FormComponent(), function ($browser) {
                    $browser->whenAvailable('@via-resource-field', function ($browser) {
                        $browser->assertSee('User')->assertSee('Taylor Otwell');
                    })
                        ->whenAvailable('@attachable-select', function ($browser) {
                            $browser->assertDisabled('')
                                ->assertSelected('', '1');
                        })
                        ->assertDisabled('@attachable-select')
                        ->assertInputValue('@notes', 'Test Notes')
                        ->type('@notes', 'Test Notes Updated');
                })
                ->update()
                ->waitForText('The resource was updated!');

            $this->assertEquals('Test Notes Updated', User::with('roles')->find(1)->roles->first()->pivot->notes);

            $browser->blank();
        });
    }

    public function test_attached_resource_can_be_updated_and_can_continue_editing()
    {
        $role = RoleFactory::new()->create();
        $role->users()->attach(1, ['notes' => 'Test Notes']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->within(new IndexComponent('roles'), function ($browser) {
                    $browser->waitForTable()
                        ->click('@1-edit-attached-button');
                })
                ->on(UpdateAttached::belongsToMany('users', 1, 'roles', 1))
                ->within(new BreadcrumbComponent(), function ($browser) {
                    $browser->assertSeeLink('Users')
                        ->assertSeeLink('User Details: Taylor Otwell')
                        ->assertCurrentPageTitle('Update attached Role: 1');
                })
                ->within(new FormComponent(), function ($browser) {
                    $browser->whenAvailable('@via-resource-field', function ($browser) {
                        $browser->assertSee('User')->assertSee('Taylor Otwell');
                    })
                        ->whenAvailable('@attachable-select', function ($browser) {
                            $browser->assertDisabled('')
                                ->assertSelected('', '1');
                        })
                        ->type('@notes', 'Test Notes Updated');
                })
                ->updateAndContinueEditing()
                ->waitForText('The resource was updated!')
                ->on(UpdateAttached::belongsToMany('users', 1, 'roles', 1))
                ->whenAvailable('@attachable-select', function ($browser) {
                    $browser->assertDisabled('')
                        ->assertSelected('', '1');
                });

            $this->assertEquals('Test Notes Updated', User::with('roles')->find(1)->roles->first()->pivot->notes);

            $browser->blank();
        });
    }

    public function test_validation_errors_are_displayed()
    {
        $role = RoleFactory::new()->create();
        $role->users()->attach(1, ['notes' => 'Test Notes']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->within(new IndexComponent('roles'), function ($browser) {
                    $browser->waitForTable()
                        ->click('@1-edit-attached-button');
                })
                ->on(UpdateAttached::belongsToMany('users', 1, 'roles', 1))
                ->within(new BreadcrumbComponent(), function ($browser) {
                    $browser->assertSeeLink('Users')
                        ->assertSeeLink('User Details: Taylor Otwell')
                        ->assertCurrentPageTitle('Update attached Role: 1');
                })
                ->within(new FormComponent(), function ($browser) {
                    $browser->whenAvailable('@via-resource-field', function ($browser) {
                        $browser->assertSee('User')->assertSee('Taylor Otwell');
                    })
                        ->whenAvailable('@attachable-select', function ($browser) {
                            $browser->assertDisabled('')
                                ->assertSelected('', '1');
                        })
                        ->type('@notes', str_repeat('A', 30));
                })
                ->update()
                ->waitForText('There was a problem submitting the form.')
                ->assertSee(__('validation.max.string', ['attribute' => 'notes', 'max' => 20]))
                ->cancel();

            $this->assertEquals('Test Notes', User::with('roles')->find(1)->roles->first()->pivot->notes);

            $browser->blank();
        });
    }

    public function test_it_can_update_attached_duplicate_relations_pivot()
    {
        Carbon::setTestNow($now = Carbon::now());

        DB::table('book_purchases')->insert([
            ['user_id' => 1, 'book_id' => 4, 'type' => 'gift', 'price' => 3400, 'purchased_at' => $now->toDatetimeString()],
            ['user_id' => 1, 'book_id' => 4, 'type' => 'gift', 'price' => 3260, 'purchased_at' => $now->toDatetimeString()],
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->within(new IndexComponent('books', 'giftBooks'), function ($browser) {
                    $browser->waitForTable()
                        ->within('tr[data-pivot-id="2"]', function ($browser) {
                            $browser->assertSee('$32.60')
                                ->click('@4-edit-attached-button');
                        });
                })
                ->on(UpdateAttached::belongsToMany('users', 1, 'books', 4))
                ->within(new BreadcrumbComponent(), function ($browser) {
                    $browser->assertSeeLink('Users')
                        ->assertSeeLink('User Details: Taylor Otwell')
                        ->assertCurrentPageTitle('Update attached Book: laravel-testing-decoded');
                })
                ->within(new FormComponent(), function ($browser) {
                    $browser->whenAvailable('@via-resource-field', function ($browser) {
                        $browser->assertSee('User')->assertSee('Taylor Otwell');
                    })
                        ->whenAvailable('@price', function ($browser) {
                            $browser->assertValue('', 32.60)
                                ->type('', '43');
                        });
                })
                ->update()
                ->waitForText('The resource was updated!')
                ->on(new Detail('users', 1))
                ->within(new IndexComponent('books', 'giftBooks'), function ($browser) {
                    $browser->waitForTable()
                        ->within('tr[data-pivot-id="1"]', function ($browser) {
                            $browser->assertSee('$34.00');
                        })
                        ->within('tr[data-pivot-id="2"]', function ($browser) {
                            $browser->assertSee('$43.00');
                        });
                });

            $browser->blank();
        });
    }
}
