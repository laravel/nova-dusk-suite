<?php

namespace Tests\Browser;

use App\Flight;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\IndexComponent;
use Tests\DuskTestCase;

class CustomFieldTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function resource_can_be_created()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Create('flights'))
                    ->type('@name', 'Test Flight')
                    ->create();

            $flight = Flight::latest()->first();
            $browser->assertPathIs('/nova/resources/flights/'.$flight->id);

            $this->assertEquals('Test Flight', $flight->name);
        });
    }

    /**
     * @test
     */
    public function validation_errors_are_displayed()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Create('flights'))
                    ->create()
                    ->assertSee('The name field is required.');
        });
    }

    /**
     * @test
     */
    public function custom_index_field_displays_value()
    {
        $this->seed();

        $flight = factory(Flight::class)->create();

        $this->browse(function (Browser $browser) use ($flight) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Index('flights'))
                    ->within(new IndexComponent('flights'), function ($browser) use ($flight) {
                        $browser->assertSee($flight->name);
                    });
        });
    }

    /**
     * @test
     */
    public function custom_detail_field_displays_value()
    {
        $this->seed();

        $flight = factory(Flight::class)->create();

        $this->browse(function (Browser $browser) use ($flight) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('flights', $flight->id))
                    ->pause(250)
                    ->assertSee($flight->name);
        });
    }
}
