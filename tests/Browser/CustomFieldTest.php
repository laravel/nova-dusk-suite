<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Flight;
use App\Models\User;
use Database\Factories\FlightFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\Index;
use Laravel\Nova\Tests\DuskTestCase;

class CustomFieldTest extends DuskTestCase
{
    /**
     * @test
     */
    public function resource_can_be_created()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Create('flights'))
                    ->type('@name', 'Test Flight')
                    ->create();

            $flight = Flight::latest()->first();
            $browser->assertPathIs('/nova/resources/flights/'.$flight->id);

            $this->assertEquals('Test Flight', $flight->name);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function validation_errors_are_displayed()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Create('flights'))
                    ->create()
                    ->waitForText('There was a problem submitting the form.', 15)
                    ->assertSee('The Name field is required.');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function custom_index_field_displays_value()
    {
        $flight = FlightFactory::new()->create();

        $this->browse(function (Browser $browser) use ($flight) {
            $browser->loginAs(User::find(1))
                    ->visit(new Index('flights'))
                    ->within(new IndexComponent('flights'), function ($browser) use ($flight) {
                        $browser->assertSee($flight->name);
                    });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function custom_detail_field_displays_value()
    {
        $flight = FlightFactory::new()->create();

        $this->browse(function (Browser $browser) use ($flight) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('flights', $flight->id))
                    ->waitForTextIn('h1', 'Flight Details', 25)
                    ->assertSee($flight->name);

            $browser->blank();
        });
    }
}
