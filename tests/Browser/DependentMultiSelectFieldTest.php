<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Profile;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\FormComponent;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Tests\DuskTestCase;

class DependentMultiSelectFieldTest extends DuskTestCase
{
    public function test_it_can_apply_depends_on_creating()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Create('profiles'))
                ->within(new FormComponent(), function ($browser) {
                    $browser->selectRelation('users', 4)
                        ->assertSelectHasOptions('@interests', [
                            'laravel',
                            'phpunit',
                            'hack',
                        ])->select('@interests', ['laravel', 'hack'])
                        ->type('@github_url', 'https://github.com/taylorotwell')
                        ->pause(2000)
                        ->assertSelectMissingOptions('@interests', [
                            'hack',
                        ]);
                })->create()
                ->waitForText('The profile was created!');

            $browser->blank();

            $profile = Profile::latest()->first();

            $this->assertSame(['laravel'], $profile->interests);
        });
    }

    public function test_it_can_apply_depends_on_updating()
    {
        Profile::whereKey(1)->update([
            'github_url' => '',
            'interests' => ['laravel', 'hack'],
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Update('profiles', 1))
                ->within(new FormComponent(), function ($browser) {
                    $browser->assertSelectHasOptions('@interests', [
                        'laravel',
                        'phpunit',
                        'hack',
                    ])->assertSelected('@interests', 'laravel')
                        ->assertSelected('@interests', 'hack')
                        ->type('@github_url', 'https://github.com/taylorotwell')
                        ->pause(2000)
                        ->assertSelectMissingOptions('@interests', [
                            'hack',
                        ]);
                })->update()
                ->waitForText('The profile was updated!');

            $browser->blank();

            $profile = Profile::find(1);

            $this->assertSame(['laravel'], $profile->interests);
        });
    }
}
