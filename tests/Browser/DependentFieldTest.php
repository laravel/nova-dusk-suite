<?php

namespace Laravel\Nova\Tests\Browser;

use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Tests\DuskTestCase;

class DependentFieldTest extends DuskTestCase
{
    /** @test */
    public function it_can_apply_depends_on_first_load()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(4)
                ->visit(new Create('posts'))
                ->waitForTextIn('h1', 'Create User Post')
                ->assertMissing('@attachment');

            $browser->blank();
        });
    }
}
