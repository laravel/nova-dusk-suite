<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\RoleFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Attach;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class AttachTest extends DuskTestCase
{
    /**
     * @test
     */
    public function resource_can_be_attached()
    {
        $role = RoleFactory::new()->create();

        $this->browse(function (Browser $browser) use ($role) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->within(new IndexComponent('roles'), function ($browser) {
                        $browser->waitFor('@attach-button')
                                ->click('@attach-button');
                    })
                    ->on(new Attach('users', 1, 'roles'))
                    ->whenAvailable('@via-resource-field', function ($browser) {
                        $browser->assertSee('User')->assertSee('1');
                    })
                    ->selectAttachable($role->id)
                    ->clickAttach();

            $this->assertDatabaseHas('role_user', [
                'user_id' => '1',
                'role_id' => '1',
                'notes' => null,
            ]);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function fields_on_intermediate_table_should_be_stored()
    {
        $this->whileSearchable(function () {
            $role = RoleFactory::new()->create();

            $this->browse(function (Browser $browser) use ($role) {
                $browser->loginAs(User::find(1))
                        ->visit(new Detail('users', 1))
                        ->within(new IndexComponent('roles'), function ($browser) {
                            $browser->waitFor('@attach-button')
                                    ->click('@attach-button');
                        })
                        ->on(new Attach('users', 1, 'roles'))
                        ->whenAvailable('@via-resource-field', function ($browser) {
                            $browser->assertSee('User')->assertSee('1');
                        })
                        ->selectAttachable($role->id)
                        ->type('@notes', 'Test Notes')
                        ->clickAttach()
                        ->waitFor('[dusk="roles-index-component"] table', 30);

                $this->assertDatabaseHas('role_user', [
                    'user_id' => '1',
                    'role_id' => '1',
                    'notes' => 'Test Notes',
                ]);

                $browser->blank();
            });
        });
    }

    /**
     * @test
     */
    public function validation_errors_are_displayed()
    {
        $role = RoleFactory::new()->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->within(new IndexComponent('roles'), function ($browser) {
                        $browser->waitFor('@attach-button')
                                ->click('@attach-button');
                    })
                    ->on(new Attach('users', 1, 'roles'))
                    ->whenAvailable('@via-resource-field', function ($browser) {
                        $browser->assertSee('User')->assertSee('1');
                    })
                    ->clickAttach()
                    ->waitForText('There was a problem submitting the form.', 15)
                    ->assertSee('The role field is required.');

            $this->assertDatabaseMissing('role_user', [
                'user_id' => '1',
                'role_id' => '1',
            ]);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function it_display_attachable_resource_based_on_relationship()
    {
        Carbon::setTestNow($now = Carbon::now());

        DB::table('book_purchases')->insert([
            ['user_id' => 1, 'book_id' => 3, 'type' => 'personal', 'price' => 39, 'purchased_at' => $now->toDatetimeString()],
            ['user_id' => 1, 'book_id' => 4, 'type' => 'gift', 'price' => 34, 'purchased_at' => $now->toDatetimeString()],
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->within(new IndexComponent('books', 'giftBooks'), function ($browser) {
                        $browser->waitForTable(25)
                            ->assertSeeResource(4)
                            ->assertDontSeeResource(3);
                    })
                    ->within(new IndexComponent('books', 'personalBooks'), function ($browser) {
                        $browser->waitForTable(25)
                            ->assertSeeResource(3)
                            ->assertDontSeeResource(4);
                    });
        });
    }

    /**
     * @test
     */
    public function it_can_attach_different_relation_groups()
    {
        Carbon::setTestNow($now = Carbon::now());

        $this->browse(function (Browser $browser) use ($now) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->within(new IndexComponent('books', 'giftBooks'), function ($browser) {
                        $browser->waitFor('@attach-button')
                                ->click('@attach-button');
                    })
                    ->on(new Attach('users', 1, 'books'))
                    ->waitFor('.content form', 25)
                    ->assertSeeIn('h1', 'Attach Book')
                    ->selectAttachable(4)
                    ->type('@price', '39')
                    ->type('[dusk="purchased_at"] + input', $now->toDatetimeString())
                    ->clickAttach()
                    ->within(new IndexComponent('books', 'giftBooks'), function ($browser) {
                        $browser->waitForTable(25)
                            ->assertSeeResource(4);
                    })
                    ->within(new IndexComponent('books', 'personalBooks'), function ($browser) {
                        $browser->assertSee('No book matched the given criteria.');
                    });
        });

        $this->assertDatabaseHas('book_purchases', [
            'user_id' => '1',
            'book_id' => '4',
            'price' => 39,
            'type' => 'gift',
        ]);
    }

    /**
     * @test
     */
    public function it_can_attach_duplicate_relations_with_different_pivot()
    {
        Carbon::setTestNow($now = Carbon::now());

        DB::table('book_purchases')->insert([
            ['user_id' => 1, 'book_id' => 4, 'type' => 'gift', 'price' => 34, 'purchased_at' => $now->copy()->subDay(1)->toDatetimeString()],
        ]);

        $this->browse(function (Browser $browser) use ($now) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->within(new IndexComponent('books', 'personalBooks'), function ($browser) {
                        $browser->waitFor('@attach-button')
                                ->click('@attach-button');
                    })
                    ->on(new Attach('users', 1, 'books'))
                    ->waitFor('.content form', 25)
                    ->assertSeeIn('h1', 'Attach Book')
                    ->selectAttachable(4)
                    ->type('@price', '34')
                    ->type('[dusk="purchased_at"] + input', $now->toDatetimeString())
                    ->clickAttach()
                    ->within(new IndexComponent('books', 'personalBooks'), function ($browser) {
                        $browser->waitForTable(25)
                            ->assertSeeResource(4);
                    })
                    ->within(new IndexComponent('books', 'personalBooks'), function ($browser) {
                        $browser->waitForTable(25)
                            ->assertSeeResource(4);
                    });
        });
    }

    /**
     * @test
     * @group local-time
     */
    public function it_cannot_attach_duplicate_relations_with_same_pivot()
    {
        Carbon::setTestNow($now = Carbon::parse('2021-02-16 12:55:29'));

        DB::table('book_purchases')->insert([
            ['user_id' => 1, 'book_id' => 4, 'type' => 'gift', 'price' => 34, 'purchased_at' => $now->toDatetimeString()],
        ]);

        $this->browse(function (Browser $browser) use ($now) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->within(new IndexComponent('books', 'giftBooks'), function ($browser) {
                        $browser->waitFor('@attach-button')
                                ->click('@attach-button');
                    })
                    ->on(new Attach('users', 1, 'books'))
                    ->waitFor('.content form', 25)
                    ->assertSeeIn('h1', 'Attach Book')
                    ->selectAttachable(4)
                    ->type('@price', '34')
                    ->type('[dusk="purchased_at"] + input', $now->copy()->timezone(env('DUSK_TIMEZONE'))->toDatetimeString())
                    ->clickAttach()
                    ->waitForText('There was a problem submitting the form.', 15)
                    ->assertSee('This books is already attached.');
        });
    }
}
