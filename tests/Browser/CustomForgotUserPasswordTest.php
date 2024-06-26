<?php

namespace Laravel\Nova\Tests\Browser;

use App\Providers\NovaServiceProvider;
use App\Providers\NovaWithoutAuthenticationServiceProvider;
use Database\Factories\UserFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Nova;
use Laravel\Nova\Tests\DuskTestCase;
use Orchestra\Testbench\Attributes\WithConfig;
use PHPUnit\Framework\Attributes\Group;

#[Group('auth')]
#[WithConfig('mail.default', 'log')]
class CustomForgotUserPasswordTest extends DuskTestCase
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app)
    {
        return collect(parent::getPackageProviders($app))
            ->replace([
                NovaServiceProvider::class => NovaWithoutAuthenticationServiceProvider::class,
            ])->all();
    }

    public function test_it_redirect_to_login_after_password_reset()
    {
        $this->beforeServingApplication(function ($app, $config) {
            $config->set('mail.default', 'log');
        });

        $this->browse(function (Browser $browser) {
            $user = UserFactory::new()->create();

            $browser->logout()
                ->assertGuest()
                ->visit(Nova::url('password/reset'))
                ->waitForText('Forgot your password?')
                ->type('input[id="email"]', $user->email)
                ->clickAndWaitForReload('button[type="submit"]', 40)
                ->assertPathIs('/login');

            $browser->blank();
        });
    }
}
