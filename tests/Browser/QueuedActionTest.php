<?php

namespace Laravel\Nova\Tests\Browser;

use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class QueuedActionTest extends DuskTestCase
{
    /**
     * @test
     */
    public function queued_action_status_is_displayed_in_action_events_list()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->runAction('sleep')
                ->within(new IndexComponent('action-events'), function ($browser) {
                    $browser->waitForTable()
                        ->scrollIntoView('')
                        ->assertSee('Sleep')
                        ->assertSee('FINISHED');
                });

            $browser->blank();
        });
    }
}
