<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Tests\DuskTestCase;

class SearchableSelectTest extends DuskTestCase
{
    public function test_it_can_search_select()
    {
        $this->defineApplicationStates('searchable');

        $this->browse(function (Browser $browser) {
            $user = User::with('profile')->find(1);

            $this->assertNotSame('America/Chicago', $user->profile->timezone);

            $browser->loginAs($user)
                ->visit(new Update('profiles', $user->id))
                ->searchAndSelectFirstResult('timezone', 'America/Chicago')
                ->update()
                ->waitForText('The profile was updated!');

            $browser->blank();

            $user->refresh();

            $this->assertSame('America/Chicago', $user->profile->timezone);
        });
    }
}
