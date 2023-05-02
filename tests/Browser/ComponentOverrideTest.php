<?php

namespace Laravel\Nova\Tests\Browser;

use Laravel\Dusk\Browser;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Tests\DuskTestCase;

class ComponentOverrideTest extends DuskTestCase
{
    public function test_it_can_override_default_components()
    {
        $this->beforeServingApplication(function ($app) {
            Nova::serving(function (ServingNova $event) {
                Nova::script('component-override', __DIR__.'/assets/component-override.js');
            });
        });

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Create('users'))
                ->click('.custom-help-component')
                ->assertDialogOpened('HelpText was overriden using component-override.js')
                ->dismissDialog();

            $browser->blank();
        });
    }
}
