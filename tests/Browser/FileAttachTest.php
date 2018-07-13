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

            $captain = Captain::orderBy('id', 'desc')->first();
            $this->assertNotNull($captain->photo);
            Storage::disk('public')->exists($captain->photo);
        });
    }
}
