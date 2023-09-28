<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\UserFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\UserIndex;
use Laravel\Nova\Tests\DuskTestCase;

class PaginationTest extends DuskTestCase
{
    public function test_it_can_navigate_from_exceeding_page_using_simple_pagination()
    {
        UserFactory::new()->times(21)->create();

        User::whereKey(1)->update([
            'settings' => ['pagination' => 'simple'],
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex(['users_page' => 2]))
                ->within(new IndexComponent('users'), static function ($browser) {
                    $browser->waitForEmptyDialog()
                        ->assertDisabled('nav button[dusk="next"]')
                        ->assertEnabled('nav button[dusk="previous"]')
                        ->previousPage();
                })->on(new UserIndex())
                ->assertQueryStringHas('users_page', 1);

            $browser->blank();
        });
    }

    public function test_it_can_navigate_from_exceeding_page_using_links_pagination()
    {
        UserFactory::new()->times(21)->create();

        User::whereKey(1)->update([
            'settings' => ['pagination' => 'links'],
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex(['users_page' => 2]))
                ->within(new IndexComponent('users'), static function ($browser) {
                    $browser->waitForEmptyDialog()
                        ->assertDisabled('nav button[dusk="next"]')
                        ->assertDisabled('nav button[dusk="last"]')
                        ->assertEnabled('nav button[dusk="previous"]')
                        ->assertEnabled('nav button[dusk="first"]')
                        ->previousPage();
                })->on(new UserIndex())
                ->assertQueryStringHas('users_page', 1);

            $browser->blank();
        });
    }
}
