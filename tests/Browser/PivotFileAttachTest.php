<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Captain;
use Database\Factories\CaptainFactory;
use Database\Factories\ShipFactory;
use Illuminate\Support\Facades\Storage;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Attach;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\UpdateAttached;
use Laravel\Nova\Tests\DuskTestCase;

class PivotFileAttachTest extends DuskTestCase
{
    /**
     * @test
     */
    public function file_can_be_attached_to_resource()
    {
        $this->whileSearchable(function () {
            $captain = CaptainFactory::new()->create();
            $ship = ShipFactory::new()->create();

            $this->browse(function (Browser $browser) use ($captain, $ship) {
                $browser->loginAs(1)
                        ->visit(new Attach('captains', $captain->id, 'ships'))
                        ->searchAndSelectFirstRelation('ships', $ship->id)
                        ->attach('@contract', __DIR__.'/Fixtures/Document.pdf')
                        ->clickAttach();

                // Verify the photo in the information in the database...
                $captain = Captain::orderBy('id', 'desc')->first();
                $ship = $captain->ships()->get()->first();
                $this->assertNotNull($path = $ship->pivot->contract);
                Storage::disk('public')->assertExists($path);

                // Ensure file is not removed on blank update...
                $browser->visit(new UpdateAttached('captains', $captain->id, 'ships', $ship->id))
                        ->update();

                $captain = Captain::orderBy('id', 'desc')->first();
                $ship = $captain->ships()->get()->first();
                $this->assertNotNull($path = $ship->pivot->contract);
                Storage::disk('public')->assertExists($path);

                // Detach the record...
                $browser->visit(new Detail('captains', $captain->id))
                        ->within(new IndexComponent('ships'), function ($browser) use ($ship) {
                            $browser->waitForTable(25)
                                    ->deleteResourceById($ship->id)
                                    ->waitForText('No ship matched the given criteria.', 25);
                        });

                $browser->blank();

                // Validate file no longer exists.
                Storage::disk('public')->assertMissing($path);
            });
        });
    }

    /**
     * @test
     */
    public function file_can_be_detached_from_edit_attached_screen()
    {
        $this->whileSearchable(function () {
            $captain = CaptainFactory::new()->create();
            $ship = ShipFactory::new()->create();

            $this->browse(function (Browser $browser) use ($captain, $ship) {
                $browser->loginAs(1)
                        ->visit(new Attach('captains', $captain->id, 'ships'))
                        ->searchAndSelectFirstRelation('ships', $ship->id)
                        ->attach('@contract', __DIR__.'/Fixtures/Document.pdf')
                        ->clickAttach();

                // Verify the photo in the information in the database...
                $captain = Captain::orderBy('id', 'desc')->first();
                $ship = $captain->ships()->get()->first();
                $this->assertNotNull($path = $ship->pivot->contract);
                Storage::disk('public')->assertExists($path);

                // Delete the file...
                $browser->visit(new UpdateAttached('captains', $captain->id, 'ships', $ship->id))
                        ->click('@contract-internal-delete-link')
                        ->pause(250)
                        ->click('@confirm-upload-delete-button')
                        ->waitForText('The file was deleted!');

                $browser->blank();

                // Validate file no longer exists.
                Storage::disk('public')->assertMissing($path);
            });
        });
    }
}
