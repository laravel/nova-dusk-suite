<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\CaptainFactory;
use Database\Factories\DockFactory;
use Database\Factories\ShipFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\UserIndex;
use Laravel\Nova\Tests\DuskTestCase;

class FilterableBelongsToManyFieldTest extends DuskTestCase
{
    public function test_it_can_filter_belongs_to_many_field()
    {
        DB::table('book_purchases')->insert([
            ['user_id' => 1, 'book_id' => 4, 'type' => 'gift', 'price' => 3900, 'purchased_at' => Carbon::yesterday()->toDatetimeString()],
            ['user_id' => 1, 'book_id' => 4, 'type' => 'gift', 'price' => 3400, 'purchased_at' => Carbon::now()->toDatetimeString()],
            ['user_id' => 1, 'book_id' => 3, 'type' => 'gift', 'price' => 3400, 'purchased_at' => Carbon::now()->toDatetimeString()],
            ['user_id' => 2, 'book_id' => 4, 'type' => 'gift', 'price' => 3900, 'purchased_at' => Carbon::now()->toDatetimeString()],
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->assertSeeResource(1)
                        ->assertSeeResource(2)
                        ->assertSeeResource(3)
                        ->assertSeeResource(4)
                        ->runFilter(function ($browser) {
                            $browser->whenAvailable('@giftBooks-eloquent-field-filter', function ($browser) {
                                $browser->select('', 4)->pause(1000);
                            });
                        })->waitForTable()
                        ->assertSeeResource(1)
                        ->assertSeeResource(2)
                        ->assertDontSeeResource(3)
                        ->assertDontSeeResource(4)
                        ->runFilter(function ($browser) {
                            $browser->whenAvailable('@giftBooks-eloquent-field-filter', function ($browser) {
                                $browser->select('', 3)->pause(1000);
                            });
                        })->waitForTable()
                        ->assertSeeResource(1)
                        ->assertDontSeeResource(2)
                        ->assertDontSeeResource(3)
                        ->assertDontSeeResource(4);
                });

            $browser->blank();
        });
    }

    public function test_it_can_filter_belongs_to_many_field_via_relationship()
    {
        DB::table('book_purchases')->insert([
            ['user_id' => 1, 'book_id' => 4, 'type' => 'gift', 'price' => 3900, 'purchased_at' => Carbon::yesterday()->toDatetimeString()],
            ['user_id' => 1, 'book_id' => 4, 'type' => 'gift', 'price' => 3400, 'purchased_at' => Carbon::now()->toDatetimeString()],
            ['user_id' => 1, 'book_id' => 3, 'type' => 'gift', 'price' => 3400, 'purchased_at' => Carbon::now()->toDatetimeString()],
            ['user_id' => 2, 'book_id' => 4, 'type' => 'gift', 'price' => 3900, 'purchased_at' => Carbon::now()->toDatetimeString()],
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->within(new IndexComponent('books', 'giftBooks'), function ($browser) {
                    $browser->waitForTable()
                        ->assertSeeResource(4, 1)
                        ->assertSeeResource(4, 2)
                        ->assertSeeResource(3, 3)
                        ->assertDontSeeResource(4, 4)
                        ->runFilter(function ($browser) {
                            $browser->whenAvailable('@giftBooks-eloquent-field-filter', function ($browser) {
                                $browser->select('', 4)->pause(1000);
                            });
                        })
                        ->waitForTable()
                        ->assertQueryStringHas(
                            'books_filter',
                            'W3sicmVzb3VyY2U6Ym9va3M6Z2lmdEJvb2tzIjo0fSx7IkJvb2xlYW46YWN0aXZlIjoiIn0seyJDdXJyZW5jeTpwcmljZSI6W251bGwsbnVsbF19XQ=='
                        )
                        ->assertSeeResource(4, 1)
                        ->assertSeeResource(4, 2)
                        ->assertDontSeeResource(3, 3)
                        ->assertDontSeeResource(4, 4)
                        ->runFilter(function ($browser) {
                            $browser->whenAvailable('@giftBooks-eloquent-field-filter', function ($browser) {
                                $browser->select('', 3)->pause(1000);
                            });
                        })->waitForTable()
                        ->assertQueryStringHas(
                            'books_filter',
                            'W3sicmVzb3VyY2U6Ym9va3M6Z2lmdEJvb2tzIjozfSx7IkJvb2xlYW46YWN0aXZlIjoiIn0seyJDdXJyZW5jeTpwcmljZSI6W251bGwsbnVsbF19XQ=='
                        )
                        ->assertDontSeeResource(4, 1)
                        ->assertDontSeeResource(4, 2)
                        ->assertSeeResource(3, 3)
                        ->assertDontSeeResource(4, 4);
                });

            $browser->blank();
        });
    }

    public function test_it_can_filter_belongs_to_many_field_via_nested_relationship()
    {
        $this->browse(function (Browser $browser) {
            [$dock, $dock1] = DockFactory::new()->times(2)->create();
            [$captain, $captain1] = CaptainFactory::new()->times(2)->create();
            [$ship, $ship1, $ship2] = ShipFactory::new()->times(3)->create([
                'dock_id' => $dock->getKey(),
            ]);
            [$ship3, $ship4, $ship5] = ShipFactory::new()->times(5)->create([
                'dock_id' => $dock1->getKey(),
            ]);

            $ship->captains()->sync([$captain->getKey(), $captain1->getKey()]);
            $ship3->captains()->sync([$captain1->getKey()]);

            $browser->loginAs(1)
                ->visit(new Detail('docks', $dock->getKey()))
                ->within(new IndexComponent('ships'), function ($browser) use ($captain, $captain1, $ship, $ship1, $ship2) {
                    $browser->waitForTable()
                        ->assertSeeResource($ship->getKey())
                        ->assertSeeResource($ship1->getKey())
                        ->assertSeeResource($ship2->getKey())
                        ->runFilter(function ($browser) use ($captain) {
                            $browser->whenAvailable('@captains-eloquent-field-filter', function ($browser) use ($captain) {
                                $browser->select('', $captain->getKey())->pause(1000);
                            });
                        })->waitForTable()
                        ->assertQueryStringHas(
                            'ships_filter',
                            base64_encode(json_encode([['resource:captains:captains' => $captain->getKey()]]))
                        )
                        ->assertSeeResource($ship->getKey())
                        ->assertDontSeeResource($ship1->getKey())
                        ->assertDontSeeResource($ship2->getKey())
                        ->runFilter(function ($browser) use ($captain1) {
                            $browser->whenAvailable('@captains-eloquent-field-filter', function ($browser) use ($captain1) {
                                $browser->select('', $captain1->getKey())->pause(1000);
                            });
                        })->waitForTable()
                        ->assertQueryStringHas(
                            'ships_filter',
                            base64_encode(json_encode([['resource:captains:captains' => $captain1->getKey()]]))
                        )
                        ->assertSeeResource($ship->getKey())
                        ->assertDontSeeResource($ship1->getKey())
                        ->assertDontSeeResource($ship2->getKey());
                });

            $browser->blank();
        });
    }
}
