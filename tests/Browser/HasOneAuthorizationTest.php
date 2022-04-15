<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class HasOneAuthorizationTest extends DuskTestCase
{
    /** @test */
    public function it_can_create_users_without_authorization_to_profile()
    {
        User::find(1)->shouldBlockFrom('profile.create');

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new Create('users'))
                    ->assertMissing('@create-profile-relation-button')
                    ->type('@name', 'Adam Wathan')
                    ->type('@email', 'adam@laravel.com')
                    ->type('@password', 'secret')
                    ->select('@settings.pagination', 'simple')
                    ->create()
                    ->waitForText('The user was created!');

            $user = User::latest()->first();

            $browser->on(new Detail('users', $user->id));

            $browser->blank();
        });
    }

    /** @test */
    public function it_can_view_user_detail_without_authorization_to_profile()
    {
        User::find(1)->shouldBlockFrom('profile.view.2');

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new Detail('users', 2))
                    ->assertMissing('@profiles-detail-component')
                    ->visit(new Detail('users', 1))
                    ->waitFor('@profiles-detail-component')
                    ->assertSee('Profile');

            $browser->blank();
        });
    }
}
