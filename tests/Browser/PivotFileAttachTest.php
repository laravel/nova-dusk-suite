<?php

namespace Tests\Browser;

use App\Ship;
use App\User;
use App\Captain;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Support\Facades\Storage;
use Tests\Browser\Components\IndexComponent;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PivotFileAttachTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function file_can_be_attached_to_resource()
    {
        $this->seed();

        $captain = factory(Captain::class)->create();
        $ship = factory(Ship::class)->create();

        $this->browse(function (Browser $browser) use ($captain, $ship) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Attach('captains', $captain->id, 'ships'))
                    ->selectAttachable($ship->id)
                    ->attach('@contract', __DIR__.'/Fixtures/Document.pdf')
                    ->clickAttach();

            // Verify the photo in the information in the database...
            $captain = Captain::orderBy('id', 'desc')->first();
            $ship = $captain->ships()->get()->first();
            $this->assertNotNull($ship->pivot->contract);
            $this->assertTrue(Storage::disk('public')->exists($ship->pivot->contract));

            // Ensure file is not removed on blank update...
            $browser->visit(new Pages\UpdateAttached('captains', $captain->id, 'ships', $ship->id))
                    ->update();

            $captain = Captain::orderBy('id', 'desc')->first();
            $ship = $captain->ships()->get()->first();
            $this->assertNotNull($path = $ship->pivot->contract);
            $this->assertTrue(Storage::disk('public')->exists($ship->pivot->contract));

            // Detach the record...
            $browser->visit(new Pages\Detail('captains', $captain->id))
                    ->within(new IndexComponent('ships'), function ($browser) use ($ship) {
                        $browser->deleteResourceById($ship->id);
                    });

            // Clean up the file...
            $this->assertFalse(Storage::disk('public')->exists($path));
        });
    }

    /**
     * @test
     */
    public function file_can_be_detached_from_edit_attached_screen()
    {
        $this->seed();

        $captain = factory(Captain::class)->create();
        $ship = factory(Ship::class)->create();

        $this->browse(function (Browser $browser) use ($captain, $ship) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Attach('captains', $captain->id, 'ships'))
                    ->selectAttachable($ship->id)
                    ->attach('@contract', __DIR__.'/Fixtures/Document.pdf')
                    ->clickAttach();

            // Verify the photo in the information in the database...
            $captain = Captain::orderBy('id', 'desc')->first();
            $ship = $captain->ships()->get()->first();
            $this->assertNotNull($path = $ship->pivot->contract);
            $this->assertTrue(Storage::disk('public')->exists($ship->pivot->contract));

            // Delete the file...
            $browser->visit(new Pages\UpdateAttached('captains', $captain->id, 'ships', $ship->id))
                    ->click('@contract-internal-delete-link')
                    ->pause(250)
                    ->click('@confirm-upload-delete-button')
                    ->pause(250);

            // Clean up the file...
            $this->assertFalse(Storage::disk('public')->exists($path));
        });
    }
}
