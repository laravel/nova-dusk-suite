<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\UserFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Dashboard;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class GlobalSearchTest extends DuskTestCase
{
    public function test_can_search_resource_as_big_int()
    {
        $user = UserFactory::new()->create([
            'id' => 9121018173229432287,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs(1)
                    ->visit(new Dashboard())
                    ->within('@global-search-component', function ($browser) use ($user) {
                        $browser->type('@global-search', $user->getKey())
                            ->whenAvailable('button[dusk="users 0"]', function ($browser) {
                                $browser->click('');
                            });
                    })
                    ->waitForLocation('/nova/resources/users/'.$user->getKey())
                    ->on(new Detail('users', $user->getKey()));

            $browser->blank();
        });
    }
}