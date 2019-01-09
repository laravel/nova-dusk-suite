<?php

namespace Tests\Browser;

use App\User;
use App\Captain;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class FileDeleteTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function file_can_be_deleted()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                ->visit(new Pages\Create('captains'))
                ->type('@name', 'Taylor Otwell')
                ->attach('@photo', __DIR__ . '/Fixtures/StardewTaylor.png')
                ->create();

            $captain = Captain::orderBy('id', 'desc')->first();

            $this->assertTrue(Storage::disk('public')->exists($captain->photo));

            $browser->visit(new Pages\Detail('captains', $captain->id))
                ->delete();

            $this->assertFalse(Storage::disk('public')->exists($captain->photo));
            $this->assertEmpty(Captain::query()->get());
        });
    }
}