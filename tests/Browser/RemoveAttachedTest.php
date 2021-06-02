<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
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
            ['user_id' => 1, 'book_id' => 4, 'type' => 'personal', 'price' => 34, 'purchased_at' => $now->toDatetimeString()],
            ['user_id' => 1, 'book_id' => 4, 'type' => 'personal', 'price' => 32, 'purchased_at' => $now->toDatetimeString()],
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->within(new IndexComponent('books', 'personalBooks'), function ($browser) {
                        $browser->waitForTable()
                            ->within('tr[data-pivot-id="2"]', function ($browser) {
                                $browser->click('@4-delete-button')
                                    ->elsewhere('', function ($browser) {
                                        $browser->whenAvailable('.modal', function ($browser) {
                                            $browser->click('#confirm-delete-button');
                                        });
                                    })->pause(500);
                            });
                    });
        });

        $this->assertDatabaseHas('book_purchases', [
            'user_id' => 1,
            'book_id' => 4,
            'price' => 34,
        ]);

        $this->assertDatabaseMissing('book_purchases', [
            'user_id' => 1,
            'book_id' => 4,
            'price' => 32,
        ]);
    }
}
