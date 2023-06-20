<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\RoleFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\Controls\RelationSelectControlComponent;
use Laravel\Nova\Testing\Browser\Components\FormComponent;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Attach;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class AttachDuplicationTest extends DuskTestCase
{
    public function test_it_cant_attach_different_unique_relation()
    {
        $this->browse(function (Browser $browser) {
            $role = RoleFactory::new()->create();

            $browser->loginAs(1)
                ->visit(Attach::belongsToMany('users', 1, 'roles'))
                ->within(new FormComponent(), function ($browser) use ($role) {
                    $browser->whenAvailable('@via-resource-field', function ($browser) {
                        $browser->assertSee('User')->assertSee('Taylor Otwell');
                    })
                        ->selectAttachable($role->id);
                })
                ->create()
                ->waitForText('The resource was attached!')
                ->on(new Detail('users', 1))
                ->waitForTextIn('h1', 'User Details: Taylor Otwell')
                ->visit(Attach::belongsToMany('users', 1, 'roles'))
                ->whenAvailable('@via-resource-field', function ($browser) {
                    $browser->assertSee('User')->assertSee('Taylor Otwell');
                })
                ->whenAvailable(new RelationSelectControlComponent('attachable'), function ($browser) use ($role) {
                    $browser->assertSelectMissingOption('', $role->id);
                });

            $this->assertDatabaseHas('role_user', [
                'user_id' => '1',
                'role_id' => '1',
                'notes' => null,
            ]);

            $browser->blank();
        });
    }

    /**
     * @group datetime-field
     */
    public function test_it_can_attach_different_relation_groups()
    {
        Carbon::setTestNow($now = Carbon::now());

        $this->browse(function (Browser $browser) use ($now) {
            $browser->loginAs(1)
                ->visit(Attach::belongsToMany('users', 1, 'books', 'giftBooks'))
                ->assertSeeIn('h1', 'Attach Book')
                ->within(new FormComponent(), function ($browser) use ($now) {
                    $browser->selectAttachable(4)
                        ->type('@price', '39')
                        ->typeOnDateTimeLocal('input[dusk="purchased_at"]', $now);
                })
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
     * @group datetime-field
     */
    public function test_it_can_attach_duplicate_relations_with_different_pivot()
    {
        Carbon::setTestNow($now = Carbon::now());

        DB::table('book_purchases')->insert([
            ['user_id' => 1, 'book_id' => 4, 'type' => 'gift', 'price' => 3400, 'purchased_at' => $now->copy()->subDay(1)->toDatetimeString()],
        ]);

        $this->browse(function (Browser $browser) use ($now) {
            $browser->loginAs(1)
                ->visit(Attach::belongsToMany('users', 1, 'books', 'personalBooks'))
                ->assertSeeIn('h1', 'Attach Book')
                ->within(new FormComponent(), function ($browser) use ($now) {
                    $browser->selectAttachable(4)
                        ->type('@price', '34')
                        ->typeOnDateTimeLocal('input[dusk="purchased_at"]', $now);
                })
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
     * @group datetime-field
     */
    public function test_it_cannot_attach_duplicate_relations_with_same_pivot()
    {
        Carbon::setTestNow($now = Carbon::parse('2021-02-16 12:55:00'));

        DB::table('book_purchases')->insert([
            ['user_id' => 1, 'book_id' => 4, 'type' => 'gift', 'price' => 3400, 'purchased_at' => $now->toDatetimeString()],
        ]);

        $this->browse(function (Browser $browser) use ($now) {
            $browser->loginAs(1)
                ->visit(Attach::belongsToMany('users', 1, 'books', 'giftBooks'))
                ->assertSeeIn('h1', 'Attach Book')
                ->selectAttachable(4)
                ->type('@price', '34.00')
                ->typeOnDateTimeLocal('input[dusk="purchased_at"]', $now)
                ->create()
                ->waitForText('There was a problem submitting the form.')
                ->assertSee('This books is already attached.')
                ->cancel();

            $browser->blank();
        });
    }
}
