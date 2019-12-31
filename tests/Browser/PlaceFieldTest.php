<?php

namespace Tests\Browser;

use App\Address;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PlaceFieldTest extends DuskTestCase
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
                    ->visit(new Pages\Create('addresses'))
                    ->click('@address_line_1')
                    ->type('@address_line_1', '110 Kingsbrook St Hot Springs')
                    ->pause(2000)
                    ->keys('@address_line_1', '{arrow_down}', '{enter}')
                    ->create();

            $address = Address::latest('id')->first();

            $browser->assertPathIs('/nova/resources/addresses/'.$address->id);

            $this->assertEquals('110 Kingsbrook Street', $address->address_line_1);
            $this->assertNull($address->address_line_2);
            $this->assertEquals('Hot Springs', $address->city);
            $this->assertEquals('AR', $address->state);
            $this->assertEquals('71901', $address->postal_code);
            $this->assertEquals('US', $address->country);
        });
    }
}
