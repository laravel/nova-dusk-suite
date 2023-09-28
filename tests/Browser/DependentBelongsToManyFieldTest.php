<?php

namespace Laravel\Nova\Tests\Browser;

use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\Controls\RelationSelectControlComponent;
use Laravel\Nova\Testing\Browser\Components\FormComponent;
use Laravel\Nova\Testing\Browser\Pages\Attach;
use Laravel\Nova\Tests\DuskTestCase;

class DependentBelongsToManyFieldTest extends DuskTestCase
{
    public function test_it_can_listen_to_related_field_changes()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Attach('users', 1, 'books', 'personalBooks'))
                ->within(new FormComponent(), static function ($browser) {
                    $browser->assertSee('Attach Book')
                        ->assertSeeIn('p.help-text', 'Price starts from $0-$99')
                        ->within(new RelationSelectControlComponent('attachable'), static function ($browser) {
                            $browser->select('', 1);
                        })->pause(500)
                        ->assertSeeIn('p.help-text', 'Price starts from $10-$199');
                });
        });
    }
}
