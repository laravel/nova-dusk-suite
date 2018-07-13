<?php

namespace Tests\Browser;

use App\Dock;
use App\Ship;
use App\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Support\Facades\Hash;
use Tests\Browser\Components\IndexComponent;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class DateTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function resource_can_be_created()
    {
        $this->seed();

        $dock = factory(Dock::class)->create();

        $date = now()->subHours(1);

        $this->browse(function (Browser $browser) use ($dock, $date) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('docks', $dock->id))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->click('@create-button');
                    })
                    ->on(new Pages\Create('ships'))
                    ->type('@name', 'Titanic')
                    ->type('@departed_at', $date->setTimezone(env('DUSK_TIMEZONE'))->format('Y-m-d H:i:s'))
                    ->create();

            $ship = Ship::orderBy('id', 'desc')->first();

            $browser->assertPathIs('/nova/resources/ships/'.$ship->id);

            $this->assertEquals(
                $date->setTimezone(env('DUSK_TIMEZONE'))->format('Y-m-d H:i:s'),
                $ship->departed_at->setTimezone(env('DUSK_TIMEZONE'))->format('Y-m-d H:i:s')
            );
        });
    }
}
