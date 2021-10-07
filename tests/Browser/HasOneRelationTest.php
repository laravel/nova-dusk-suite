<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Tests\DuskTestCase;

class HasOneRelationTest extends DuskTestCase
{
    /** @test */
    public function has_one_relation_does_not_add_duplicate_using_create_button()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->within(new IndexComponent('profiles'), function ($browser) {
                        $browser->assertMissing('@create-button');
                    });

            $browser->blank();
        });
    }

    /** @test */
    public function has_one_relation_does_not_have_create_and_add_another_button()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 4))
                    ->runCreateRelation('profiles')
                    ->assertMissing('@create-and-add-another-button');

            $browser->blank();
        });
    }

    /** @test */
    public function can_create_resource_with_inline_has_one_relationship()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Create('users'))
                    ->type('@name', 'Adam Wathan')
                    ->type('@email', 'adam@laravel.com')
                    ->type('@password', 'secret')
                    ->click('@create-profile-relation-button')
                    ->type('@github_url', 'https://github.com/adamwathan')
                    ->type('@twitter_url', 'https://twitter.com/adamwathan')
                    ->select('select[dusk="timezone"]', 'UTC')
                    ->select('select[dusk="interests"]', ['laravel', 'phpunit'])
                    ->create()
                    ->waitForText('The user was created!');

            $user = User::with('profile')->orderBy('id', 'desc')->first();

            $browser->on(new Detail('users', $user->id));

            $this->assertSame('Adam Wathan', $user->name);
            $this->assertSame('adam@laravel.com', $user->email);
            $this->assertTrue(Hash::check('secret', $user->password));
            $this->assertTrue($user->active);
            $this->assertSame('https://github.com/adamwathan', $user->profile->github_url);
            $this->assertSame('https://twitter.com/adamwathan', $user->profile->twitter_url);
            $this->assertSame('UTC', $user->profile->timezone);
            $this->assertSame(['laravel', 'phpunit'], $user->profile->interests);

            $browser->blank();
        });
    }

    /** @test */
    public function can_create_inline_has_one_relationship_on_existing_resource()
    {
        $this->browse(function (Browser $browser) {
            $user = User::find(4);

            $browser->loginAs($user)
                    ->visit(new Update('users', $user->id))
                    ->click('@create-profile-relation-button')
                    ->type('@github_url', 'https://github.com/laravel/nova')
                    ->select('select[dusk="timezone"]', 'UTC')
                    ->select('select[dusk="interests"]', ['laravel', 'phpunit', 'vue'])
                    ->update()
                    ->waitForText('The user was updated!')
                    ->on(new Detail('users', $user->id));

            $user->refresh()->load('profile');

            $this->assertSame('https://github.com/laravel/nova', $user->profile->github_url);
            $this->assertNull($user->profile->twitter_url);
            $this->assertSame('UTC', $user->profile->timezone);
            $this->assertSame(['laravel', 'phpunit', 'vue'], $user->profile->interests);

            $browser->blank();
        });
    }

    /** @test */
    public function can_update_inline_has_one_relationship_on_existing_resource()
    {
        $this->browse(function (Browser $browser) {
            $user = User::find(1);

            $browser->loginAs($user)
                    ->visit(new Update('users', $user->id))
                    ->type('@github_url', 'https://github.com/laravel')
                    ->type('@twitter_url', 'https://twitter.com/laravelphp')
                    ->select('select[dusk="timezone"]', 'UTC')
                    ->select('select[dusk="interests"]', ['laravel', 'phpunit', 'vue'])
                    ->update()
                    ->waitForText('The user was updated!')
                    ->on(new Detail('users', $user->id));

            $user->refresh()->load('profile');

            $this->assertSame('https://github.com/laravel', $user->profile->github_url);
            $this->assertSame('https://twitter.com/laravelphp', $user->profile->twitter_url);
            $this->assertSame('UTC', $user->profile->timezone);
            $this->assertSame(['laravel', 'phpunit', 'vue'], $user->profile->interests);

            $browser->blank();
        });
    }
}
