<?php

namespace Tests\Browser;

use App\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\IndexComponent;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class QueuedActionTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function queued_action_status_is_displayed_in_action_events_list()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->runAction('sleep')
                    ->within(new IndexComponent('action-events'), function ($browser) {
                        $browser->pause(250);

                        $browser->assertSee('Sleep')
                                ->assertSee('Finished');
                    });
        });
    }
}
