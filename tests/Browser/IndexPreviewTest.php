<?php

namespace Laravel\Nova\Tests\Browser;

use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Components\Modals\PreviewResourceModalComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\UserIndex;
use Laravel\Nova\Tests\DuskTestCase;

class IndexPreviewTest extends DuskTestCase
{
    /** @test */
    public function it_can_display_preview_modal()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->previewResourceById(1)
                        ->elsewhereWhenAvailable(new PreviewResourceModalComponent(), function ($browser) {
                            $browser->assertSee('PREVIEWING TAYLOR OTWELL')
                                ->assertSeeIn('@name', 'Taylor Otwell')
                                ->assertSeeIn('@email', 'taylor@laravel.com')
                                ->assertViewButtonVisible()
                                ->view();
                        });
                })->on(new Detail('users', 1));
        });
    }
}
