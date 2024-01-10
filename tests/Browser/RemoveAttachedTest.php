<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\BookPurchase;
use Illuminate\Support\Carbon;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Components\Modals\DeleteResourceModalComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class RemoveAttachedTest extends DuskTestCase
{
    public function test_it_can_remove_attached_duplicate_relations_pivot()
    {
        Carbon::setTestNow($now = Carbon::now());

        $book = BookPurchase::forceCreate([
            'user_id' => 1,
            'book_id' => 4,
            'type' => 'gift',
            'price' => 3400,
            'purchased_at' => $now->toDatetimeString(),
        ]);

        $book2 = BookPurchase::forceCreate([
            'user_id' => 1,
            'book_id' => 4,
            'type' => 'gift',
            'price' => 3200,
            'purchased_at' => $now->toDatetimeString(),
        ]);

        $this->browse(function (Browser $browser) use ($book, $book2) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->within(new IndexComponent('books', 'giftBooks'), function ($browser) use ($book, $book2) {
                    $browser->waitForTable()
                        ->assertPresent("tr[data-pivot-id='{$book->getKey()}']")
                        ->assertPresent("tr[data-pivot-id='{$book2->getKey()}']")
                        ->within("tr[data-pivot-id='{$book2->getKey()}']", function ($browser) {
                            $browser->click('@4-delete-button')
                                ->elsewhereWhenAvailable(new DeleteResourceModalComponent(), function ($browser) {
                                    $browser->confirm();
                                });
                        })->waitForTable()
                        ->assertPresent("tr[data-pivot-id='{$book->getKey()}']")
                        ->assertMissing("tr[data-pivot-id='{$book2->getKey()}']");
                });

            $browser->blank();
        });

        $this->assertDatabaseHas('book_purchases', [
            'id' => $book->getKey(),
            'user_id' => 1,
            'book_id' => 4,
            'price' => 3400,
            'type' => 'gift',
        ]);

        $this->assertDatabaseMissing('book_purchases', [
            'id' => $book2->getKey(),
            'user_id' => 1,
            'book_id' => 4,
            'price' => 3200,
            'type' => 'gift',
        ]);
    }
}
