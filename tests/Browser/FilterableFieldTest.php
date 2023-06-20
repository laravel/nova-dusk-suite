<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Profile;
use App\Models\User;
use Database\Factories\PostFactory;
use Database\Factories\SubscriberFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Components\SearchInputComponent;
use Laravel\Nova\Testing\Browser\Pages\Index;
use Laravel\Nova\Testing\Browser\Pages\UserIndex;
use Laravel\Nova\Tests\DuskTestCase;

class FilterableFieldTest extends DuskTestCase
{
    public function test_it_can_filter_boolean_field()
    {
        User::whereIn('id', [2, 4])->update(['active' => true]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex())
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->assertFilterCount(0)
                        ->assertSeeResource(1)
                        ->assertSeeResource(2)
                        ->assertSeeResource(3)
                        ->assertSeeResource(4)
                        ->runFilter(function ($browser) {
                            $browser->click('@active-default-boolean-field-filter');
                        })
                        ->waitForTable()
                        ->assertFilterCount(1)
                        ->assertDontSeeResource(1)
                        ->assertSeeResource(2)
                        ->assertDontSeeResource(3)
                        ->assertSeeResource(4)
                        ->runFilter(function ($browser) {
                            $browser->click('@active-default-boolean-field-filter');
                        })
                        ->waitForTable()
                        ->assertFilterCount(1)
                        ->assertSeeResource(1)
                        ->assertDontSeeResource(2)
                        ->assertSeeResource(3)
                        ->assertDontSeeResource(4)
                        ->runFilter(function ($browser) {
                            $browser->click('@active-default-boolean-field-filter');
                        })
                        ->waitForTable()
                        ->assertFilterCount(0)
                        ->assertSeeResource(1)
                        ->assertSeeResource(2)
                        ->assertSeeResource(3)
                        ->assertSeeResource(4);
                });

            $browser->blank();
        });
    }

    public function test_it_can_filter_belongs_to_field()
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
                        ->assertFilterCount(1)
                        ->assertSeeResource(1)
                        ->assertSeeResource(2)
                        ->assertSeeResource(3)
                        ->assertDontSeeResource(4)
                        ->assertDontSeeResource(5);

                    $browser->runFilter(function ($browser) {
                        $browser->select('select[dusk="user-default-belongs-to-field-filter"]', 2);
                    })
                        ->waitForTable()
                        ->assertFilterCount(1)
                        ->assertDontSeeResource(1)
                        ->assertDontSeeResource(2)
                        ->assertDontSeeResource(3)
                        ->assertSeeResource(4)
                        ->assertSeeResource(5);

                    $browser->runFilter(function ($browser) {
                        $browser->select('select[dusk="user-default-belongs-to-field-filter"]', '');
                    })
                        ->waitForTable()
                        ->assertFilterCount(0)
                        ->assertSeeResource(1)
                        ->assertSeeResource(2)
                        ->assertSeeResource(3)
                        ->assertSeeResource(4)
                        ->assertSeeResource(5);
                });

            $browser->blank();
        });
    }

    public function test_it_can_filter_email_field()
    {
        $this->browse(function (Browser $browser) {
            $subscribers = SubscriberFactory::new()->times(5)->create();

            $browser->loginAs(1)
                ->visit(new Index('subscribers'))
                ->within(new IndexComponent('subscribers'), function ($browser) use ($subscribers) {
                    $browser->waitForTable()
                        ->assertSeeResource(1)
                        ->assertSeeResource(2)
                        ->assertSeeResource(3)
                        ->assertSeeResource(4)
                        ->assertSeeResource(5)
                        ->runFilter(function ($browser) use ($subscribers) {
                            $browser->type('@email-default-email-field-filter', $subscribers[2]->email);
                        })
                        ->waitForTable()
                        ->assertFilterCount(1)
                        ->assertDontSeeResource(1)
                        ->assertDontSeeResource(2)
                        ->assertSeeResource(3)
                        ->assertDontSeeResource(4)
                        ->assertDontSeeResource(5);
                });
        });
    }

    public function test_it_can_filter_searchable_belongs_to_field()
    {
        PostFactory::new()->times(3)->create(['user_id' => 1]);
        PostFactory::new()->times(2)->create(['user_id' => 2]);

        $this->defineApplicationStates('searchable');

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Index('posts'))
                ->within(new IndexComponent('posts'), function ($browser) {
                    $browser->waitForTable()
                        ->runFilter(function ($browser) {
                            $browser->within(new SearchInputComponent('user-default-belongs-to-field', 'filter'), function ($browser) {
                                $browser->searchFirstRelation(1);
                            });
                        })
                        ->waitForTable()
                        ->assertFilterCount(1)
                        ->assertSeeResource(1)
                        ->assertSeeResource(2)
                        ->assertSeeResource(3)
                        ->assertDontSeeResource(4)
                        ->assertDontSeeResource(5);

                    $browser->runFilter(function ($browser) {
                        $browser->within(new SearchInputComponent('user-default-belongs-to-field', 'filter'), function ($browser) {
                            $browser->searchFirstRelation(2);
                        });
                    })
                        ->waitForTable()
                        ->assertFilterCount(1)
                        ->assertDontSeeResource(1)
                        ->assertDontSeeResource(2)
                        ->assertDontSeeResource(3)
                        ->assertSeeResource(4)
                        ->assertSeeResource(5);

                    $browser->runFilter(function ($browser) {
                        $browser->click('@user-default-belongs-to-field-search-filter-clear-button');
                    })
                        ->waitForTable()
                        ->assertFilterCount(0)
                        ->assertSeeResource(1)
                        ->assertSeeResource(2)
                        ->assertSeeResource(3)
                        ->assertSeeResource(4)
                        ->assertSeeResource(5);

                    $browser->runFilter(function ($browser) {
                        $browser->within(new SearchInputComponent('user-default-belongs-to-field', 'filter'), function ($browser) {
                            $browser->assertEmptySearchResult();
                        });
                    });
                });

            $browser->blank();
        });
    }

    public function test_it_can_filter_multiselect_field()
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

    public function test_it_can_filter_date_field()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex())
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->runFilter(function ($browser) {
                            $browser->whenAvailable('input[dusk="created_at-default-date-time-field-range-start"]', function ($browser) {
                                $browser->typeOnDate('', now()->startOfMonth());
                            });
                        })->waitForTable()
                        ->assertFilterCount(1)
                        ->resetFilter()
                        ->waitForTable()
                        ->assertFilterCount(0)
                        ->runFilter(function ($browser) {
                            $browser->whenAvailable('input[dusk="created_at-default-date-time-field-range-start"]', function ($browser) {
                                $browser->typeOnDate('', now()->startOfMonth());
                            });
                        })->waitForTable()
                        ->assertFilterCount(1);
                });

            $browser->blank();
        });
    }
}
