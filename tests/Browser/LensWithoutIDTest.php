<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Book;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\LensComponent;
use Laravel\Nova\Testing\Browser\Pages\Lens;
use Laravel\Nova\Tests\DuskTestCase;

class LensWithoutIDTest extends DuskTestCase
{
    public function test_it_can_be_displayed()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Lens('books', 'book-catalogues'))
                ->within(new LensComponent('books', 'book-catalogues'), function ($browser) {
                    $browser->waitForTable();

                    foreach (Book::all() as $book) {
                        $browser->assertMissing("@{$book->id}-checkbox")
                            ->assertSee($book->sku)
                            ->assertSee($book->title)
                            ->assertMissing("@{$book->id}-control-selector")
                            ->assertMissing("@{$book->id}-view-button")
                            ->assertMissing("@{$book->id}-edit-button")
                            ->assertMissing("@{$book->id}-delete-button");
                    }
                });
        });
    }
}
