<?php

namespace Laravel\Nova\Tests\Browser;

use Illuminate\Support\Carbon;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Attach;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class DependentPivotFieldTest extends DuskTestCase
{
    /**
     * @group datetime-field
     *
     * @covers \Laravel\Nova\Fields\Currency::dependsOn()
     * @covers \Laravel\Nova\Fields\Hidden::dependsOn()
     * @covers \Laravel\Nova\Fields\Select::dependsOn()
     */
    public function test_it_can_apply_depends_on_pivot_fields()
    {
        Carbon::setTestNow($now = Carbon::now());

        $this->browse(function (Browser $browser) use ($now) {
            $browser->loginAs(1)
                ->visit(Attach::belongsToMany('books', 2, 'users', 'purchasers'))
                ->assertSeeIn('h1', 'Attach User')
                ->selectAttachable(4)
                ->assertEnabled('select[dusk="type"]')
                ->type('@price', '0')
                ->typeOnDateTimeLocal('input[dusk="purchased_at"]', $now)
                ->whenAvailable('select[dusk="type"]', function ($browser) {
                    $browser->assertDisabled('')
                        ->assertSelected('', 'gift');
                })
                ->create()
                ->waitForText('The resource was attached!')
                ->on(new Detail('books', 2))
                ->visit(new Detail('users', 4))
                ->within(new IndexComponent('books', 'giftBooks'), function ($browser) {
                    $browser->waitForTable()
                        ->assertSeeResource(2);
                })
                ->within(new IndexComponent('books', 'personalBooks'), function ($browser) {
                    $browser->waitForEmptyDialog()
                        ->assertSee('No Book matched the given criteria.');
                });

            $browser->blank();
        });

        $this->assertDatabaseHas('book_purchases', [
            'user_id' => '4',
            'book_id' => '2',
            'price' => 0,
            'type' => 'gift',
        ]);
    }
}
