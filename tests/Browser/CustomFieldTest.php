<?php

namespace Tests\Browser;

use App\User;
use App\Flight;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

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
}
