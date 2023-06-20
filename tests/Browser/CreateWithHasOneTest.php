<?php

namespace Laravel\Nova\Tests\Browser;

use Illuminate\Support\Carbon;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\SearchInputComponent;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class CreateWithHasOneTest extends DuskTestCase
{
    public function test_has_one_should_be_filled()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Create('people'))
                ->type('@name', 'Adam Wathan')
                ->typeOnDate('@date_of_birth', Carbon::createFromDate(2017, 11, 1))
                ->create()
                ->waitForText('The person was created!')
                ->visit(new Detail('people', 1))
                ->runCreateRelation('employees')
                ->whenAvailable(new SearchInputComponent('people'), function ($browser) {
                    $browser->assertSelectedSearchResult('Adam Wathan');
                })
                ->cancel();

            $browser->blank();
        });
    }

    public function test_has_one_doesnt_open_on_pressing_enter_key()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Create('users'))
                ->keys('@name', ['{enter}'])
                ->waitForText('There was a problem submitting the form.')
                ->assertSee(__('validation.required', ['attribute' => 'Name']))
                ->assertSee(__('validation.required', ['attribute' => 'Email']))
                ->assertSee(__('validation.required', ['attribute' => 'Password']))
                ->assertVisible('@create-profile-relation-button')
                ->cancel();

            $browser->blank();
        });
    }
}
