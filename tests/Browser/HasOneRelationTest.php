<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class HasOneRelationTest extends DuskTestCase
{
    /**
     * @test
     */
    public function has_one_relation_does_not_add_duplicate_using_create_button()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->within(new IndexComponent('profiles'), function ($browser) {
                        $browser->waitForTable()
                                ->assertMissing('@create-button');
                    });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function has_one_relation_does_not_have_create_and_add_another_button()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 4))
                    ->within(new IndexComponent('profiles'), function ($browser) {
                        $browser->whenAvailable('@create-button', function ($browser) {
                            $browser->click('');
                        });
                    })
                    ->on(new Create('profiles'))
                    ->waitFor('#nova .content form')
                    ->assertMissing('@create-and-add-another-button');

            $browser->blank();
        });
    }
}
