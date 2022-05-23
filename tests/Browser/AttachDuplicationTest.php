<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\RoleFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Attach;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class AttachDuplicationTest extends DuskTestCase
{
    /**
     * @test
     */
    public function it_cant_attach_different_unique_relation()
    {
        $role = RoleFactory::new()->create();

        $this->browse(function (Browser $browser) use ($role) {
            $browser->loginAs(1)
                    ->visit(new Detail('users', 1))
                    ->runAttachRelation('roles')
                    ->whenAvailable('@via-resource-field', function ($browser) {
                        $browser->assertSee('User')->assertSee('1');
                    })
                    ->assertSelectHasOptions('@attachable-select', [$role->id])
                    ->selectAttachable($role->id)
                    ->create()
                    ->waitForText('The resource was attached!')
                    ->on(new Detail('users', 1))
                    ->waitForTextIn('h1', 'User Details: 1')
                    ->visit(new Attach('users', 1, 'roles'))
                    ->whenAvailable('@via-resource-field', function ($browser) {
                        $browser->assertSee('User')->assertSee('1');
                    })
                    ->assertSelectMissingOptions('@attachable-select', [$role->id]);

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
     * @group local-time
     */
    public function it_can_attach_different_relation_groups()
    {
        Carbon::setTestNow($now = Carbon::now());

        $this->browse(function (Browser $browser) use ($now) {
            $browser->loginAs(1)
                    ->visit(new Detail('users', 1))
                    ->runAttachRelation('books', 'giftBooks')
                    ->assertSeeIn('h1', 'Attach Book')
                    ->selectAttachable(4)
                    ->type('@price', '39')
                    ->typeOnDateTimeLocal('input[dusk="purchased_at"]', $now)
                    ->create()
                    ->waitForText('The resource was attached!')
                    ->on(new Detail('users', 1))
                    ->within(new IndexComponent('books', 'giftBooks'), function ($browser) {
                        $browser->waitForTable()
                            ->assertSeeResource(4);
                    })
                    ->within(new IndexComponent('books', 'personalBooks'), function ($browser) {
                        $browser->waitForEmptyDialog()
                                ->assertSee('No Book matched the given criteria.');
                    });

            $browser->blank();
        });

        $this->assertDatabaseHas('book_purchases', [
            'user_id' => '1',
            'book_id' => '4',
            'price' => 3900,
            'type' => 'gift',
        ]);
    }

    /**
     * @test
     * @group local-time
     */
    public function it_can_attach_duplicate_relations_with_different_pivot()
    {
        Carbon::setTestNow($now = Carbon::now());

        DB::table('book_purchases')->insert([
            ['user_id' => 1, 'book_id' => 4, 'type' => 'gift', 'price' => 3400, 'purchased_at' => $now->copy()->subDay(1)->toDatetimeString()],
        ]);

        $this->browse(function (Browser $browser) use ($now) {
            $browser->loginAs(1)
                    ->visit(new Detail('users', 1))
                    ->runAttachRelation('books', 'personalBooks')
                    ->assertSeeIn('h1', 'Attach Book')
                    ->selectAttachable(4)
                    ->type('@price', '34')
                    ->typeOnDateTimeLocal('input[dusk="purchased_at"]', $now)
                    ->create()
                    ->waitForText('The resource was attached!')
                    ->within(new IndexComponent('books', 'personalBooks'), function ($browser) {
                        $browser->waitForTable()
                            ->assertSeeResource(4);
                    })
                    ->within(new IndexComponent('books', 'personalBooks'), function ($browser) {
                        $browser->waitForTable()
                            ->assertSeeResource(4);
                    });

            $browser->blank();
        });
    }

    /**
     * @test
     * @group local-time
     */
    public function it_cannot_attach_duplicate_relations_with_same_pivot()
    {
        Carbon::setTestNow($now = Carbon::parse('2021-02-16 12:55:00'));

        DB::table('book_purchases')->insert([
            ['user_id' => 1, 'book_id' => 4, 'type' => 'gift', 'price' => 34, 'purchased_at' => $now->toDatetimeString()],
        ]);

        $this->browse(function (Browser $browser) use ($now) {
            $browser->loginAs(1)
                    ->visit(new Detail('users', 1))
                    ->runAttachRelation('books', 'giftBooks')
                    ->assertSeeIn('h1', 'Attach Book')
                    ->selectAttachable(4)
                    ->type('@price', '34')
                    ->typeOnDateTimeLocal('input[dusk="purchased_at"]', $now)
                    ->create()
                    ->waitForText('There was a problem submitting the form.')
                    ->assertSee('This books is already attached.')
                    ->click('@cancel-attach-button');

            $browser->blank();
        });
    }
}
