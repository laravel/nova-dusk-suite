<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\UserIndex;
use Laravel\Nova\Tests\DuskTestCase;

class IndexPreviewTest extends DuskTestCase
{
    /** @test */
    public function it_can_display_preview_modal()
    {
        $user = User::find(1);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable()
                                ->previewResourceById(1)
                                ->elsewhereWhenAvailable('.modal[data-modal-open=true]', function ($browser) {
                                    $browser->assertSee('PREVIEWING 1');
                                });
                    });
        });
    }
}
