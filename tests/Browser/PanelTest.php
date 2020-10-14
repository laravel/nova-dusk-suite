<?php

namespace Laravel\Nova\Tests\Browser;

use App\Address;
use App\User;
use Laravel\Dusk\Browser;
use Laravel\Nova\Tests\DuskTestCase;

/**
 * @group external-network
 */
class PanelTest extends DuskTestCase
{
    /**
     * @test
     */
    public function fields_can_be_placed_into_panels()
    {
        $this->setupLaravel();

        $address = factory(Address::class)->create();

        $this->browse(function (Browser $browser) use ($address) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('addresses', $address->id))
                    ->assertSee('More Address Details');

            $browser->blank();
        });
    }
}
