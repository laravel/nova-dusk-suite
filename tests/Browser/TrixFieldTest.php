<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Book;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Tests\DuskTestCase;

class TrixFieldTest extends DuskTestCase
{
    public function test_it_set_the_correct_html_from_existing_value()
    {
        $description = '<div>Code Happy is the best-selling title for learning version 3 of the Laravel PHP Framework.</div>';

        Book::whereKey(1)->update([
            'description' => $description,
        ]);

        $this->browse(function (Browser $browser) use ($description) {
            $browser->loginAs(1)
                ->visit(new Update('books', 1))
                ->type('@sku', 'codehappy-revised')
                ->update()
                ->waitForText('The book was updated!');

            $browser->blank();

            $this->assertDatabaseHas('books', [
                'sku' => 'codehappy-revised',
                'title' => 'Laravel: Code Happy',
                'description' => $description,
            ]);
        });
    }
}
