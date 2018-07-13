<?php

namespace Tests\Browser;

use App\User;
use App\Captain;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class FileAttachTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function file_can_be_attached_to_resource()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Create('captains'))
                    ->type('@name', 'Taylor Otwell')
                    ->attach('@photo', __DIR__.'/Fixtures/StardewTaylor.png')
                    ->create();

            // Verify the photo in the information in the database...
            $captain = Captain::orderBy('id', 'desc')->first();
            $this->assertNotNull($captain->photo);
            $this->assertTrue(Storage::disk('public')->exists($captain->photo));

            // Download the file...
            $browser->on(new Pages\Detail('captains', $captain->id))
                    ->click('@photo-download-link')
                    ->pause(250);

            // Ensure file is not removed on blank update...
            $browser->visit(new Pages\Update('captains', $captain->id))
                    ->update();

            $captain = $captain->fresh();
            $this->assertNotNull($captain->photo);
            $this->assertTrue(Storage::disk('public')->exists($captain->photo));

            // Delete the file...
            $browser->visit(new Pages\Update('captains', $captain->id))
                    ->click('@photo-delete-link')
                    ->pause(250)
                    ->click('@confirm-upload-delete-button')
                    ->pause(250);

            // Clean up the file...
            $this->assertFalse(Storage::disk('public')->exists($captain->photo));
        });
    }
}
