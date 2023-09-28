<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\UserFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\HeaderComponent;
use Laravel\Nova\Testing\Browser\Components\SidebarComponent;
use Laravel\Nova\Testing\Browser\Pages\Dashboard;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class FormButtonTest extends DuskTestCase
{
    public function test_it_can_handle_native_form_request()
    {
        $this->browse(function (Browser $browser) {
            $user = UserFactory::new()->create([
                'active' => false,
            ]);

            $browser->loginAs($user)
                ->visit(new Dashboard)
                ->within(new HeaderComponent(), function (Browser $browser) use ($user) {
                    $browser->press($user->name)
                        ->elsewhereWhenAvailable('[data-menu-open=true]', static function ($browser) {
                            $browser->press('Verify Account');
                        });
                })->on(new Detail('users', $user->id));

            $this->assertTrue($user->refresh()->active);

            $browser->blank();
        });
    }

    public function test_it_can_handle_inertia_form_request()
    {
        $this->browse(function (Browser $browser) {
            $user = UserFactory::new()->create([
                'active' => false,
            ]);

            $browser->loginAs($user)
                ->visit(new Dashboard)
                ->within(new SidebarComponent(), function (Browser $browser) {
                    $browser->press('Verify Using Inertia');
                })->on(new Detail('users', $user->id));

            $this->assertTrue($user->refresh()->active);

            $browser->blank();
        });
    }
}
