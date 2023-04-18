<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\BreadcrumbComponent;
use Laravel\Nova\Testing\Browser\Components\FormComponent;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class CreateTest extends DuskTestCase
{
    public function test_resource_can_be_created()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Create('users'))
                ->within(new BreadcrumbComponent(), function ($browser) {
                    $browser->assertSeeLink('Users')
                        ->assertCurrentPageTitle('Create User');
                })
                ->within(new FormComponent(), function ($browser) {
                    $browser->type('@name', 'Adam Wathan')
                        ->type('@email', 'adam@laravel.com')
                        ->type('@password', 'secret')
                        ->select('@settings->pagination', 'simple');
                })
                ->create()
                ->waitForText('The user was created!');

            $user = User::orderBy('id', 'desc')->first();

            $browser->on(new Create('profiles'))
                ->assertQueryStringHas('viaResource', 'users')
                ->assertQueryStringHas('viaResourceId', $user->id)
                ->assertQueryStringHas('viaRelationship', 'profile')
                ->within(new BreadcrumbComponent(), function ($browser) use ($user) {
                    $browser->assertSeeLink('Users')
                        ->assertSeeLink('User Details: '.$user->name)
                        ->assertCurrentPageTitle('Create Profile');
                })
                ->within(new FormComponent(), function ($browser) {
                    $browser->type('@github_url', 'https://github.com/adamwathan')
                        ->type('@twitter_url', 'https://twitter.com/adamwathan')
                        ->select('select[dusk="timezone"]', 'UTC')
                        ->select('select[dusk="interests"]', ['laravel', 'phpunit']);
                })
                ->create()
                ->waitForText('The profile was created!')
                ->on(new Detail('users', $user->id));

            $user->refresh()->load('profile');

            $this->assertSame('Adam Wathan', $user->name);
            $this->assertSame('adam@laravel.com', $user->email);
            $this->assertTrue(Hash::check('secret', $user->password));
            $this->assertSame('simple', $user->settings['pagination']);
            $this->assertTrue($user->active);
            $this->assertSame('https://github.com/adamwathan', $user->profile->github_url);
            $this->assertSame('https://twitter.com/adamwathan', $user->profile->twitter_url);
            $this->assertSame('UTC', $user->profile->timezone);
            $this->assertSame(['laravel', 'phpunit'], $user->profile->interests);

            $browser->blank();
        });
    }

    public function test_validation_errors_are_displayed()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Create('users'))
                ->create()
                ->waitForText('There was a problem submitting the form.')
                ->assertSee(__('validation.required', ['attribute' => 'Name']))
                ->assertSee(__('validation.required', ['attribute' => 'Email']))
                ->assertSee(__('validation.required', ['attribute' => 'Password']))
                ->cancel();

            $browser->blank();
        });
    }

    public function test_resource_can_be_created_and_another_resource_can_be_added()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Create('users'))
                ->within(new FormComponent(), function ($browser) {
                    $browser->type('@name', 'Adam Wathan')
                        ->type('@email', 'adam@laravel.com')
                        ->type('@password', 'secret');
                })
                ->createAndAddAnother()
                ->waitForText('The user was created!')
                ->on(new Create('users'));

            $user = User::orderBy('id', 'desc')->first();

            $this->assertEquals('Adam Wathan', $user->name);
            $this->assertEquals('adam@laravel.com', $user->email);
            $this->assertTrue(Hash::check('secret', $user->password));

            $browser->blank();
        });
    }
}
