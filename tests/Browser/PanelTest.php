<?php

namespace Tests\Browser;

use App\Address;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

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
