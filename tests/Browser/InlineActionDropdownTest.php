<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\CaptainFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Index;
use Laravel\Nova\Testing\Browser\Pages\UserIndex;
use Laravel\Nova\Tests\DuskTestCase;

class InlineActionDropdownTest extends DuskTestCase
{
    public function test_it_is_present_when_it_does_contains_any_items()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex())
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->assertPresentControlSelectorById(1)
                        ->assertPresentControlSelectorById(2)
                        ->assertPresentControlSelectorById(3)
                        ->assertPresentControlSelectorById(4);
                });
        });
    }

    public function test_it_is_missing_when_it_doesnt_contains_any_items()
    {
        $this->browse(function (Browser $browser) {
            $captains = CaptainFactory::new()->times(4)->create();

            $browser->loginAs(1)
                ->visit(new Index('captains'))
                ->within(new IndexComponent('captains'), function ($browser) use ($captains) {
                    $browser->waitForTable()
                        ->assertMissingControlSelectorById($captains[0]->id)
                        ->assertMissingControlSelectorById($captains[1]->id)
                        ->assertMissingControlSelectorById($captains[2]->id)
                        ->assertMissingControlSelectorById($captains[3]->id);
                });
        });
    }
}
