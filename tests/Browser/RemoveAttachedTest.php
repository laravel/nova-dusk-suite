<?php

namespace Laravel\Nova\Tests\Browser;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Components\Modals\DeleteResourceModalComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class RemoveAttachedTest extends DuskTestCase
{
    /**
     * @test
     */
    public function it_can_remove_attached_duplicate_relations_pivot()
    {
        Carbon::setTestNow($now = Carbon::now());

        DB::table('book_purchases')->insert([
            ['user_id' => 1, 'book_id' => 4, 'type' => 'gift', 'price' => 3400, 'purchased_at' => $now->toDatetimeString()],
            ['user_id' => 1, 'book_id' => 4, 'type' => 'gift', 'price' => 3200, 'purchased_at' => $now->toDatetimeString()],
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->within(new IndexComponent('books', 'giftBooks'), function ($browser) {
                    $browser->waitForTable()
                        ->within('tr[data-pivot-id="2"]', function ($browser) {
                            $browser->click('@4-delete-button')
                                ->elsewhereWhenAvailable(new DeleteResourceModalComponent(), function ($browser) {
                                    $browser->confirm();
                                })->pause(500);
                        });
                });

            $browser->blank();
        });

        $this->assertDatabaseHas('book_purchases', [
            'user_id' => 1,
            'book_id' => 4,
            'price' => 3400,
            'type' => 'gift',
        ]);

        $this->assertDatabaseMissing('book_purchases', [
            'user_id' => 1,
            'book_id' => 4,
            'price' => 3200,
            'type' => 'gift',
        ]);
    }
}
