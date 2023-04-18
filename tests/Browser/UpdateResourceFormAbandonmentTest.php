<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\VideoFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\SidebarComponent;
use Laravel\Nova\Testing\Browser\Pages\Index;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Testing\Browser\Pages\UserIndex;
use Laravel\Nova\Tests\DuskTestCase;

class UpdateResourceFormAbandonmentTest extends DuskTestCase
{
    public function test_it_shows_exit_warning_if_resource_form_has_changes_when_navigating_to_different_page()
    {
        $video = VideoFactory::new()->create([
            'title' => 'Demo',
        ]);

        $this->browse(function (Browser $browser) use ($video) {
            $browser->loginAs(1)
                ->visit(new Update('videos', $video->id))
                ->type('@title', 'Hello World')
                ->within(new SidebarComponent(), function ($browser) {
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

    public function test_it_shows_exit_warning_if_resource_form_has_changes_when_clicking_browser_back_button()
    {
        $video = VideoFactory::new()->create([
            'title' => 'Demo',
        ]);

        $this->browse(function (Browser $browser) use ($video) {
            $browser->loginAs(1)
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

    public function test_it_doesnt_show_exit_warning_if_resource_form_has_changes_when_clicking_cancel()
    {
        $video = VideoFactory::new()->create([
            'title' => 'Demo',
        ]);

        $this->browse(function (Browser $browser) use ($video) {
            $browser->loginAs(1)
                ->visit(new Index('videos'))
                ->visit(new Update('videos', $video->id))
                ->type('@title', 'Hello World')
                ->cancel()
                ->on(new Index('videos'));

            $this->assertDatabaseMissing('videos', [
                'title' => 'Hello World',
            ]);

            $browser->blank();
        });
    }

    public function test_it_doesnt_show_exit_warning_if_resource_form_after_save_and_create_another_when_clicking_cancel()
    {
        $video = VideoFactory::new()->create([
            'title' => 'Demo',
        ]);

        $this->browse(function (Browser $browser) use ($video) {
            $browser->loginAs(1)
                ->visit(new Index('videos'))
                ->visit(new Update('videos', $video->id))
                ->type('@title', 'Hello World')
                ->click('@update-and-continue-editing-button')
                ->waitForText('The user video was updated!')
                ->cancel()
                ->on(new Index('videos'));

            $this->assertDatabaseHas('videos', [
                'title' => 'Hello World',
            ]);

            $browser->blank();
        });
    }
}
