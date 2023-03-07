<?php

namespace Laravel\Nova\Tests\Browser;

use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Attach;
use Laravel\Nova\Tests\DuskTestCase;

class CreateWithInlineManyToManyRelationButtonTest extends DuskTestCase
{
    public function test_belongs_to_many_resource_should_fetch_the_related_resource_id_info()
    {
        $this->defineApplicationStates(['inline-create']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Attach('users', 1, 'roles'))
                ->runInlineCreate('roles', function ($browser) {
                    $browser->waitForText('Create Role')
                        ->type('@name', 'Manager');
                })
                ->waitForText('The role was created!')
                ->pause(500)
                ->assertSee('Manager')
                ->create()
                ->waitForText('The resource was attached!');

            $browser->blank();
        });
    }
}
