<?php

namespace Laravel\Nova\Tests\Browser;

use Carbon\CarbonImmutable;
use Database\Factories\PeopleFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Index;
use Laravel\Nova\Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\Group;

class FilterableDateFieldTest extends DuskTestCase
{
    #[Group('date-field')]
    public function test_it_can_filter_by_date_field()
    {
        $this->browse(function (Browser $browser) {
            $people = PeopleFactory::new()->times(15)->create([
                'date_of_birth' => $date = CarbonImmutable::parse('13 Dec, 1983'),
            ]);

            $people1 = PeopleFactory::new()->times(5)->create([
                'date_of_birth' => $date1 = CarbonImmutable::parse('13 Dec, 1984'),
            ]);

            $browser->loginAs(1)
                ->visit(new Index('people'))
                ->within(new IndexComponent('people'), function (Browser $browser) use ($date, $people, $people1) {
                    $browser->waitForTable()
                        ->runFilter(function ($browser) use ($date) {
                            $browser->typeOnDate('@date_of_birth-date-field-filter-range-start', $date)
                                ->typeOnDate('@date_of_birth-date-field-filter-range-end', $date->addDay(1));
                        })
                        ->waitForTable();

                    $people->each(function ($person) use ($browser) {
                        $browser->assertSeeResource($person->getKey());
                    });

                    $people1->each(function ($person) use ($browser) {
                        $browser->assertDontSeeResource($person->getKey());
                    });
                })
                ->within(new IndexComponent('people'), function (Browser $browser) use ($date1, $people, $people1) {
                    $browser->waitForTable()
                        ->runFilter(function ($browser) use ($date1) {
                            $browser->typeOnDate('@date_of_birth-date-field-filter-range-start', $date1)
                                ->typeOnDate('@date_of_birth-date-field-filter-range-end', $date1->addDay(1));
                        })
                        ->waitForTable();

                    $people->each(function ($person) use ($browser) {
                        $browser->assertDontSeeResource($person->getKey());
                    });

                    $people1->each(function ($person) use ($browser) {
                        $browser->assertSeeResource($person->getKey());
                    });
                });
        });
    }

    #[Group('datetime-field')]
    public function test_it_can_filter_by_datetime_field()
    {
        $this->browse(function (Browser $browser) {
            $people = PeopleFactory::new()->times(15)->create([
                'created_at' => $date = CarbonImmutable::parse('13 Dec, 1983'),
            ]);

            $people1 = PeopleFactory::new()->times(5)->create([
                'created_at' => $date1 = CarbonImmutable::parse('13 Dec, 1984'),
            ]);

            $browser->loginAs(1)
                ->visit(new Index('people'))
                ->within(new IndexComponent('people'), function (Browser $browser) use ($date, $people, $people1) {
                    $browser->waitForTable()
                        ->runFilter(function ($browser) use ($date) {
                            $browser->typeInDateTimeField('@created_at-date-time-field-filter-range-start', $date)
                                ->typeInDateTimeField('@created_at-date-time-field-filter-range-end', $date->addDay());
                        }, function ($browser) {
                            $browser->waitForTable();
                            $browser->closeCurrentDropdown();
                        });

                    $people->each(function ($person) use ($browser) {
                        $browser->assertSeeResource($person->getKey());
                    });

                    $people1->each(function ($person) use ($browser) {
                        $browser->assertDontSeeResource($person->getKey());
                    });
                })
                ->within(new IndexComponent('people'), function (Browser $browser) use ($date1, $people, $people1) {
                    $browser->waitForTable()
                        ->runFilter(function ($browser) use ($date1) {
                            $browser->typeInDateTimeField('@created_at-date-time-field-filter-range-start', $date1)
                                ->typeInDateTimeField('@created_at-date-time-field-filter-range-end',
                                    $date1->addDay(1));
                        }, function ($browser) {
                            $browser->waitForTable();
                        });

                    $people->each(function ($person) use ($browser) {
                        $browser->assertDontSeeResource($person->getKey());
                    });

                    $people1->each(function ($person) use ($browser) {
                        $browser->assertSeeResource($person->getKey());
                    });
                });
        });
    }
}
