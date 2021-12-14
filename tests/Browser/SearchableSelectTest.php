<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Tests\DuskTestCase;

class SearchableSelectTest extends DuskTestCase
{
    /** @test */
    public function it_can_search_select()
    {
        $this->whileSearchable(function () {
            $user = User::with('profile')->find(1);

            $this->assertNotSame('America/Chicago', $user->profile->timezone);

            $this->browse(function ($browser) use ($user) {
                $browser->loginAs($user)
                    ->visit(new Update('profiles', $user->id))
                    ->searchAndSelectFirstResult('timezone', 'America/Chicago')
                    ->update();

                $browser->blank();

                $user->refresh();

                $this->assertSame('America/Chicago', $user->profile->timezone);
            });
        });
    }
}
