<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Profile;
use Carbon\Carbon;
use Database\Factories\PostFactory;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\Index;
use Laravel\Nova\Testing\Browser\Pages\UserIndex;
use Laravel\Nova\Tests\DuskTestCase;

class FilterableFieldTest extends DuskTestCase
{
    /** @test */
    public function it_can_filter_belongs_to_field()
    {
        PostFactory::new()->times(3)->create(['user_id' => 1]);
        PostFactory::new()->times(2)->create(['user_id' => 2]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Index('posts'))
                ->within(new IndexComponent('posts'), function ($browser) {
                    $browser->waitForTable()
                        ->runFilter(function ($browser) {
                            $browser->select('select[dusk="user-default-belongs-to-field-filter"]', 1);
                        })
                        ->waitForTable()
                        ->assertSeeResource(1)
                        ->assertSeeResource(2)
                        ->assertSeeResource(3)
                        ->assertDontSeeResource(4)
                        ->assertDontSeeResource(5);

                    $browser->runFilter(function ($browser) {
                        $browser->select('select[dusk="user-default-belongs-to-field-filter"]', 2);
                    })
                        ->waitForTable()
                        ->assertDontSeeResource(1)
                        ->assertDontSeeResource(2)
                        ->assertDontSeeResource(3)
                        ->assertSeeResource(4)
                        ->assertSeeResource(5);

                    $browser->runFilter(function ($browser) {
                        $browser->select('select[dusk="user-default-belongs-to-field-filter"]', '');
                    })
                        ->waitForTable()
                        ->assertSeeResource(1)
                        ->assertSeeResource(2)
                        ->assertSeeResource(3)
                        ->assertSeeResource(4)
                        ->assertSeeResource(5);
                });

            $browser->blank();
        });
    }

    /** @test */
    public function it_can_filter_searchable_belongs_to_field()
    {
        PostFactory::new()->times(3)->create(['user_id' => 1]);
        PostFactory::new()->times(2)->create(['user_id' => 2]);

        $this->defineApplicationStates('searchable');

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Index('posts'))
                ->within(new IndexComponent('posts'), function ($browser) {
                    $searchOnEloquentFilter = function ($browser, $attribute, $search) {
                        $input = $browser->element('[dusk="'.$attribute.'-search-filter"] input');

                        if (is_null($input) || ! $input->isDisplayed()) {
                            $browser->click("@{$attribute}-search-filter")->pause(100);
                        }

                        $browser->type('[dusk="'.$attribute.'-search-filter"] input', $search);

                        $browser->pause(1500)
                                ->assertValue('[dusk="'.$attribute.'-search-filter"] input', $search)
                                ->click("@{$attribute}-search-filter-result-0")->pause(150);
                    };

                    $browser->waitForTable()
                        ->runFilter(function ($browser) use ($searchOnEloquentFilter) {
                            $searchOnEloquentFilter($browser, 'user-default-belongs-to-field', 1);
                        })
                        ->waitForTable()
                        ->assertSeeResource(1)
                        ->assertSeeResource(2)
                        ->assertSeeResource(3)
                        ->assertDontSeeResource(4)
                        ->assertDontSeeResource(5);

                    $browser->runFilter(function ($browser) use ($searchOnEloquentFilter) {
                        $searchOnEloquentFilter($browser, 'user-default-belongs-to-field', 2);
                    })
                        ->waitForTable()
                        ->assertDontSeeResource(1)
                        ->assertDontSeeResource(2)
                        ->assertDontSeeResource(3)
                        ->assertSeeResource(4)
                        ->assertSeeResource(5);

                    $browser->runFilter(function ($browser) {
                        $browser->click('@user-default-belongs-to-field-search-filter-clear-button');
                    })
                        ->waitForTable()
                        ->assertSeeResource(1)
                        ->assertSeeResource(2)
                        ->assertSeeResource(3)
                        ->assertSeeResource(4)
                        ->assertSeeResource(5);
                });

            $browser->blank();
        });
    }

    /** @test */
    public function it_can_filter_multiselect_field()
    {
        Profile::whereKey(1)->update([
            'interests' => ['laravel', 'phpunit', 'livewire', 'swoole', 'vue'],
        ]);

        Profile::whereKey(2)->update([
            'interests' => ['laravel', 'phpunit', 'swoole', 'react', 'vue'],
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Index('profiles'))
                ->within(new IndexComponent('profiles'), function ($browser) {
                    $browser->waitForTable()
                        ->runFilter(function ($browser) {
                            $browser->select('select[dusk="interests-default-multi-select-field-filter"]', ['laravel', 'phpunit']);
                        })->waitForTable()
                        ->assertSeeResource(1)
                        ->assertSeeResource(2)
                        ->assertDontSeeResource(3);

                    $browser->runFilter(function ($browser) {
                        $browser->select('select[dusk="interests-default-multi-select-field-filter"]', ['laravel', 'livewire']);
                    })
                        ->waitForTable()
                        ->assertSeeResource(1)
                        ->assertDontSeeResource(2)
                        ->assertDontSeeResource(3);
                });

            $browser->blank();
        });
    }

    /** @test */
    public function it_can_filter_belongs_to_many_field()
    {
        DB::table('book_purchases')->insert([
            ['user_id' => 1, 'book_id' => 4, 'type' => 'gift', 'price' => 39, 'purchased_at' => Carbon::yesterday()->toDatetimeString()],
            ['user_id' => 1, 'book_id' => 4, 'type' => 'gift', 'price' => 34, 'purchased_at' => Carbon::now()->toDatetimeString()],
            ['user_id' => 1, 'book_id' => 3, 'type' => 'gift', 'price' => 34, 'purchased_at' => Carbon::now()->toDatetimeString()],
            ['user_id' => 2, 'book_id' => 4, 'type' => 'gift', 'price' => 39, 'purchased_at' => Carbon::now()->toDatetimeString()],
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex())
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                            ->assertSeeResource(1)
                            ->assertSeeResource(2)
                            ->assertSeeResource(3)
                            ->assertSeeResource(4)
                            ->runFilter(function ($browser) {
                                $browser->whenAvailable('select[dusk="giftBooks-default-belongs-to-many-field-filter"]', function ($browser) {
                                    $browser->select('', 4);
                                });
                            })->waitForTable()
                            ->assertSeeResource(1)
                            ->assertSeeResource(2)
                            ->assertDontSeeResource(3)
                            ->assertDontSeeResource(4)
                            ->runFilter(function ($browser) {
                                $browser->whenAvailable('select[dusk="giftBooks-default-belongs-to-many-field-filter"]', function ($browser) {
                                    $browser->select('', 3);
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

    /** @test */
    public function it_can_filter_belongs_to_many_field_via_relationship()
    {
        DB::table('book_purchases')->insert([
            ['user_id' => 1, 'book_id' => 4, 'type' => 'gift', 'price' => 39, 'purchased_at' => Carbon::yesterday()->toDatetimeString()],
            ['user_id' => 1, 'book_id' => 4, 'type' => 'gift', 'price' => 34, 'purchased_at' => Carbon::now()->toDatetimeString()],
            ['user_id' => 1, 'book_id' => 3, 'type' => 'gift', 'price' => 34, 'purchased_at' => Carbon::now()->toDatetimeString()],
            ['user_id' => 2, 'book_id' => 4, 'type' => 'gift', 'price' => 39, 'purchased_at' => Carbon::now()->toDatetimeString()],
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
                                $browser->whenAvailable('select[dusk="giftBooks-default-belongs-to-many-field-filter"]', function ($browser) {
                                    $browser->select('', 4);
                                });
                            })->waitForTable()
                            ->assertSeeResource(4, 1)
                            ->assertSeeResource(4, 2)
                            ->assertDontSeeResource(3, 3)
                            ->assertDontSeeResource(4, 4)
                            ->runFilter(function ($browser) {
                                $browser->whenAvailable('select[dusk="giftBooks-default-belongs-to-many-field-filter"]', function ($browser) {
                                    $browser->select('', 3);
                                });
                            })->waitForTable()
                            ->assertDontSeeResource(4, 1)
                            ->assertDontSeeResource(4, 2)
                            ->assertSeeResource(3, 3)
                            ->assertDontSeeResource(4, 4);
                });

            $browser->blank();
        });
    }
}
