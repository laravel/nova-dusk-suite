<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Flight;
use Database\Factories\FlightFactory;
use Database\Factories\PassportFactory;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\FormComponent;
use Laravel\Nova\Testing\Browser\Components\SearchInputComponent;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Tests\DuskTestCase;

class TagFieldTest extends DuskTestCase
{
    public function test_it_can_attach_relationship_using_tag_field()
    {
        $this->browse(function (Browser $browser) {
            $flight = FlightFactory::new()->create();
            $passports = PassportFactory::new()->times(10)->create();

            $browser->loginAs(1)
                ->visit(new Update('flights', $flight->getKey()))
                ->within(new FormComponent(), function ($browser) use ($passports) {
                    $browser->assertMissing('@passports-selected-tags')
                        ->within(new SearchInputComponent('passports'), function ($browser) use ($passports) {
                            $browser->searchAndSelectFirstResult($passports[0]->getKey());
                        })->whenAvailable('@passports-selected-tags', function ($browser) use ($passports) {
                            $browser->assertSeeIn('span', Str::upper($passports[0]->value));
                        });
                })
                ->update()
                ->waitForText('The flight was updated!');

            $this->assertTrue(
                Flight::whereHas('passports', function ($query) use ($passports) {
                    return $query->whereIn('id', [$passports[0]->getKey()]);
                })->whereKey($flight->getKey())->exists()
            );

            $browser->blank();
        });
    }
}
