<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Flight;
use App\Models\User;
use Database\Factories\FlightFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Tests\Browser\Components\IndexComponent;
use Laravel\Nova\Tests\DuskTestCase;

class CustomFieldTest extends DuskTestCase
{
    /**
     * @test
     */
    public function resource_can_be_created()
    {
        $this->setupLaravel();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Create('flights'))
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
        $this->setupLaravel();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Create('flights'))
                    ->create()
                    ->assertSee('The Name field is required.');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function custom_index_field_displays_value()
    {
        $this->setupLaravel();

        $flight = FlightFactory::new()->create();

        $this->browse(function (Browser $browser) use ($flight) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Index('flights'))
                    ->waitFor('@flights-index-component', 5)
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
        $this->setupLaravel();

        $flight = FlightFactory::new()->create();

        $this->browse(function (Browser $browser) use ($flight) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('flights', $flight->id))
                    ->pause(250)
                    ->assertSee($flight->name);

            $browser->blank();
        });
    }
}
