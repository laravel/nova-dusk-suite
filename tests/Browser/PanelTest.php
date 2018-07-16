<?php

namespace Tests\Browser;

use App\User;
use App\Address;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PanelTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function fields_can_be_placed_into_panels()
    {
        $this->seed();

        $address = factory(Address::class)->create();

        $this->browse(function (Browser $browser) use ($address) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('addresses', $address->id))
                    ->assertSee('More Address Details');
        });
    }
}
