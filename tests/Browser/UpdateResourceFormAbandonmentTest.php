<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\VideoFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Index;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Testing\Browser\Pages\UserIndex;
use Laravel\Nova\Tests\DuskTestCase;

class UpdateResourceFormAbandonmentTest extends DuskTestCase
{
    /** @test */
    public function it_shows_exit_warning_if_resource_form_has_changes_when_navigating_to_different_page()
    {
        $video = VideoFactory::new()->create([
            'title' => 'Demo',
        ]);

        $this->browse(function (Browser $browser) use ($video) {
            $browser->loginAs(User::find(1))
                    ->visit(new Update('videos', $video->id))
                    ->type('@title', 'Hello World')
                    ->within('.sidebar-menu', function ($browser) {
                        $browser->clickLink('Users');
                    })
                    ->assertDialogOpened('Do you really want to leave? You have unsaved changes.')
                    ->acceptDialog()
                    ->on(new UserIndex)
                    ->waitForTextIn('h1', 'Users');

            $this->assertDatabaseMissing('videos', [
                'title' => 'Hello World',
            ]);

            $browser->blank();
        });
    }

    /** @test */
    public function it_shows_exit_warning_if_resource_form_has_changes_when_clicking_browser_back_button()
    {
        $video = VideoFactory::new()->create([
            'title' => 'Demo',
        ]);

        $this->browse(function (Browser $browser) use ($video) {
            $browser->loginAs(User::find(1))
                    ->visit(new Index('videos'))
                    ->visit(new Update('videos', $video->id))
                    ->type('@title', 'Hello World')
                    ->back()
                    ->pause(500)
                    ->assertDialogOpened('Do you really want to leave? You have unsaved changes.')
                    ->acceptDialog()
                    ->on(new Index('videos'));

            $this->assertDatabaseMissing('videos', [
                'title' => 'Hello World',
            ]);

            $browser->blank();
        });
    }

    /** @test */
    public function it_doesnt_show_exit_warning_if_resource_form_has_changes_when_clicking_cancel()
    {
        $video = VideoFactory::new()->create([
            'title' => 'Demo',
        ]);

        $this->browse(function (Browser $browser) use ($video) {
            $browser->loginAs(User::find(1))
                    ->visit(new Index('videos'))
                    ->visit(new Update('videos', $video->id))
                    ->type('@title', 'Hello World')
                    ->click('@cancel-update-button')
                    ->on(new Index('videos'));

            $this->assertDatabaseMissing('videos', [
                'title' => 'Hello World',
            ]);

            $browser->blank();
        });
    }
}
