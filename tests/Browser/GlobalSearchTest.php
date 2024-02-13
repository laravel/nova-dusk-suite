<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\UserFactory;
use Facebook\WebDriver\WebDriverKeys;
use Laravel\Dusk\Browser;
use Laravel\Dusk\OperatingSystem;
use Laravel\Nova\Testing\Browser\Pages\Dashboard;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class GlobalSearchTest extends DuskTestCase
{
    public function test_it_closes_the_search_results_when_search_query_is_empty()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Dashboard())
                ->within('@global-search-component', function ($browser) {
                    $browser->type('@global-search', 'a')
                        ->elsewhereWhenAvailable('@global-search-results', function ($browser) {
                            $browser->assertSee('BOOKS')->assertSee('USERS');
                        })
                        ->elsewhere('', function ($browser) {
                            $browser->assertMissing('@global-search-empty-results');
                        })
                        ->keys('@global-search', '{backspace}')
                        ->pause(1000)
                        ->elsewhere('', function ($browser) {
                            $browser->assertMissing('@global-search-results')
                                ->assertMissing('@global-search-empty-results');
                        });
                });

            $browser->blank();
        });
    }

    public function test_it_can_search_resource_as_big_int()
    {
        $user = UserFactory::new()->create([
            'id' => 9121018173229432287,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs(1)
                ->visit(new Dashboard())
                ->within('@global-search-component', function ($browser) use ($user) {
                    $browser->type('@global-search', $user->getKey())
                        ->elsewhereWhenAvailable('@global-search-results', function ($browser) {
                            $browser->click('button[dusk="users 0"]');
                        });
                })
                ->waitForLocation('/nova/resources/users/'.$user->getKey())
                ->on(new Detail('users', $user->getKey()));

            $browser->blank();
        });
    }

    public function test_it_can_use_control_click_to_open_on_new_tab()
    {
        $this->browse(function (Browser $browser) {
            $currentWindowHandles = count($browser->driver->getWindowHandles());

            $browser->loginAs(1)
                ->visit(new Dashboard())
                ->within('@global-search-component', function ($browser) {
                    $browser->type('@global-search', 'taylor')
                        ->elsewhereWhenAvailable('@global-search-results', function ($browser) {
                            $key = OperatingSystem::onMac() ? WebDriverKeys::META : WebDriverKeys::CONTROL;

                            $browser->driver->getKeyboard()->pressKey($key);

                            $browser->click('button[dusk="users 0"]');

                            $browser->driver->getKeyboard()->releaseKey($key);
                        });
                });

            $this->assertCount($currentWindowHandles + 1, $browser->driver->getWindowHandles());

            $browser->within('@global-search-component', function ($browser) {
                $browser->click('@global-search')
                        ->elsewhereWhenAvailable('@global-search-results', function ($browser) {
                            $browser->click('button[dusk="users 0"]');
                        });
            })
                ->waitForLocation('/nova/resources/users/1')
                ->on(new Detail('users', '1'));

            $this->assertCount($currentWindowHandles + 1, $browser->driver->getWindowHandles());

            $browser->blank();
        });
    }
}
