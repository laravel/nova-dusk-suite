<?php

namespace Laravel\Nova\Tests\Browser;

use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\SidebarComponent;
use Laravel\Nova\Testing\Browser\Pages\Index;
use Laravel\Nova\Testing\Browser\Pages\UserIndex;
use Laravel\Nova\Tests\DuskTestCase;

class CreateResourceFormAbandonmentTest extends DuskTestCase
{
    /** @test */
    public function it_shows_exit_warning_if_resource_form_has_changes_when_navigating_to_different_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Index('videos'))
                ->runCreate(function ($browser) {
                    $browser->keys('@title', 'Hello World', '{tab}');
                })
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

    /** @test */
    public function it_shows_exit_warning_if_resource_form_has_changes_when_clicking_browser_back_button()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Index('videos'))
                ->runCreate(function ($browser) {
                    $browser->keys('@title', 'Hello World', '{tab}');
                })
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
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Index('videos'))
                ->runCreate(function ($browser) {
                    $browser->keys('@title', 'Hello World', '{tab}');
                })
                ->cancel()
                ->on(new Index('videos'));

            $this->assertDatabaseMissing('videos', [
                'title' => 'Hello World',
            ]);

            $browser->blank();
        });
    }

    /** @test */
    public function it_doesnt_show_exit_warning_if_resource_form_after_save_and_create_another_when_clicking_cancel()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Index('videos'))
                ->runCreate(function ($browser) {
                    $browser->keys('@title', 'Hello World', '{tab}');
                })
                ->createAndAddAnother()
                ->waitForText('The user video was created!')
                ->cancel()
                ->on(new Index('videos'));

            $this->assertDatabaseHas('videos', [
                'title' => 'Hello World',
            ]);

            $browser->blank();
        });
    }
}
