<?php

namespace Tests\Browser;

use App\User;
use App\Address;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\IndexComponent;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ScoutSearchTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function resources_can_be_searched_using_scout()
    {
        $this->seed();

        factory(Address::class, 1)->create();

        $address = Address::find(random_int(1, 1));

        $this->browse(function (Browser $browser) use ($address) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Index('addresses'))
                    ->within(new IndexComponent('addresses'), function ($browser) use ($address) {
                        $browser->searchFor($address->address_line_1);
                    })
                    ->assertSee($address->address_line_1)
                    ->assertSee($address->city);
        });
    }

    /**
     * @test
     */
    public function soft_deleted_resources_can_be_searched()
    {
        $this->seed();

        factory(Address::class, 1)->create();

        $address = Address::find(random_int(1, 1));
        $address->delete();

        $this->browse(function (Browser $browser) use ($address) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Index('addresses'))
                    ->within(new IndexComponent('addresses'), function ($browser) use ($address) {
                        $browser->withTrashed()->searchFor($address->address_line_1);
                    })
                    ->assertSee($address->address_line_1)
                    ->assertSee($address->city);
        });
    }
}
