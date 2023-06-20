<?php

namespace Laravel\Nova\Tests\Browser;

use Carbon\CarbonImmutable;
use Database\Factories\PeopleFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Index;
use Laravel\Nova\Tests\DuskTestCase;

class FilterableDateFieldTest extends DuskTestCase
{
    /**
     * @group date-field
     */
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
                            $browser->typeOnDate('@date_of_birth-default-date-field-range-start', $date)
                                ->typeOnDate('@date_of_birth-default-date-field-range-end', $date->addDay(1));
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
                            $browser->typeOnDate('@date_of_birth-default-date-field-range-start', $date1)
                                ->typeOnDate('@date_of_birth-default-date-field-range-end', $date1->addDay(1));
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

    /**
     * @group datetime-field
     */
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
                            $browser->typeOnDate('@created_at-default-date-time-field-range-start', $date)
                                ->typeOnDate('@created_at-default-date-time-field-range-end', $date->addDay(1));
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
                            $browser->typeOnDate('@created_at-default-date-time-field-range-start', $date1)
                                ->typeOnDate('@created_at-default-date-time-field-range-end', $date1->addDay(1));
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
}
