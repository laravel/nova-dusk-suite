<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class CreateWithHasOneTest extends DuskTestCase
{
    public function test_has_one_should_be_filled()
    {
        $this->markTestSkipped('Need to setup nova-dusk-suite to support requirement');

        $this->setupLaravel();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit(new Detail('users', 1))
                ->within(new IndexComponent('addresses'), function ($browser) {
                    $browser->click('@create-button');
                })
                ->pause(200)
                ->assertDisabled('@user');
        });
    }
}
