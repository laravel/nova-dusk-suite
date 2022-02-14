<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\DockFactory;
use Database\Factories\ShipFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class CreateWithHasManyThroughTest extends DuskTestCase
{
    public function test_has_one_should_be_filled()
    {
        $dock = DockFactory::new()->create();
        $ship = ShipFactory::new()->create([
            'dock_id' => $dock->id,
        ]);

        $this->browse(function (Browser $browser) use ($dock, $ship) {
            $browser->loginAs(1)
                ->visit(new Detail('docks', $dock->id))
                ->within(new IndexComponent('sails'), function ($browser) {
                    $browser->waitFor('@create-button')->click('@create-button');
                })
                ->on(new Create('sails'))
                ->waitFor('[data-testid="content"] form')
                ->select('@ship', $ship->id)
                ->type('@inches', '25')
                ->create()
                ->waitForText('The sail was created!');

            $browser->blank();
        });
    }
}
