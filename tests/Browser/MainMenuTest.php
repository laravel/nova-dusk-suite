<?php

namespace Laravel\Nova\Tests\Browser;

use App\Nova\Lenses\PassthroughLens;
use App\Nova\User;
use Laravel\Dusk\Browser;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\Testing\Browser\Components\SidebarComponent;
use Laravel\Nova\Testing\Browser\Pages\Lens;
use Laravel\Nova\Testing\Browser\Pages\UserIndex;
use Laravel\Nova\Tests\DuskTestCase;

class MainMenuTest extends DuskTestCase
{
    public function test_resource_link_is_not_active_when_visiting_lens_with_custom_main_menu()
    {
        $this->beforeServingApplication(function ($app) {
            Nova::mainMenu(function () {
                return [
                    MenuSection::make('Customers', [
                        MenuItem::resource(User::class),
                        MenuItem::lens(User::class, PassthroughLens::class),
                    ])->icon('user')->collapsable(),
                ];
            });
        });

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex())
                ->within(new SidebarComponent(), function ($browser) {
                    $browser->assertPresent('@current-active-link')
                        ->assertSeeIn('@current-active-link', 'Users')
                        ->assertDontSeeIn('@current-active-link', 'Passthrough Lens');
                })
                ->visit(new Lens('users', 'passthrough-lens'))
                ->whenAvailable(new SidebarComponent(), function ($browser) {
                    $browser->assertPresent('@current-active-link')
                        ->assertDontSeeIn('@current-active-link', 'Users')
                        ->assertSeeIn('@current-active-link', 'Passthrough Lens');
                });
        });

        $this->reloadServing();
    }

    public function test_resource_link_not_active_when_visiting_lens_with_default_main_menu()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex())
                ->within(new SidebarComponent(), function ($browser) {
                    $browser->assertPresent('@current-active-link')
                        ->assertSeeIn('@current-active-link', 'Users');
                })
                ->visit(new Lens('users', 'passthrough-lens'))
                ->whenAvailable(new SidebarComponent(), function ($browser) {
                    $browser->assertMissing('@current-active-link');
                });
        });
    }
}
