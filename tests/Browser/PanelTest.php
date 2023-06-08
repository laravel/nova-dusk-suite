<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\AddressFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Tests\DuskTestCase;

class PanelTest extends DuskTestCase
{
    public function test_fields_can_be_placed_into_panels()
    {
        $address = AddressFactory::new()->create();

        $this->browse(function (Browser $browser) use ($address) {
            $browser->loginAs(1)
                ->visit(new Detail('addresses', $address->id))
                ->assertSee('More Address Details');

            $browser->blank();
        });
    }

    public function test_fields_can_be_placed_into_edit_panels()
    {
        $address = AddressFactory::new()->create();

        $this->browse(function (Browser $browser) use ($address) {
            $browser->loginAs(1)
                ->visit(new Update('addresses', $address->id))
                ->assertSee('More Address Details');

            $browser->blank();
        });
    }
}
