<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Captain;
use Database\Factories\CaptainFactory;
use Database\Factories\ShipFactory;
use Illuminate\Support\Facades\Storage;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\FormComponent;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Components\Modals\ConfirmUploadRemovalModalComponent;
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
        $this->defineApplicationStates('searchable');

        $this->browse(function (Browser $browser) {
            $captain = CaptainFactory::new()->create();
            $ship = ShipFactory::new()->create();

            $browser->loginAs(1)
                ->visit(Attach::belongsToMany('captains', $captain->id, 'ships'))
                ->within(new FormComponent(), function ($browser) use ($ship) {
                    $browser->searchFirstRelation('ships', $ship->id)
                        ->attach('@contract', __DIR__.'/Fixtures/Document.pdf');
                })
                ->create()
                ->waitForText('The resource was attached!');

            // Verify the photo in the information in the database...
            $captain = Captain::orderBy('id', 'desc')->first();
            $ship = $captain->ships()->get()->first();
            $this->assertNotNull($path = $ship->pivot->contract);
            Storage::disk('public')->assertExists($path);

            // Ensure file is not removed on blank update...
            $browser->visit(UpdateAttached::belongsToMany('captains', $captain->id, 'ships', $ship->id))
                ->update()
                ->waitForText('The resource was updated!');

            $captain = Captain::orderBy('id', 'desc')->first();
            $ship = $captain->ships()->get()->first();
            $this->assertNotNull($path = $ship->pivot->contract);
            Storage::disk('public')->assertExists($path);

            // Detach the record...
            $browser->visit(new Detail('captains', $captain->id))
                ->within(new IndexComponent('ships'), function ($browser) use ($ship) {
                    $browser->waitForTable()
                        ->deleteResourceById($ship->id)
                        ->waitForEmptyDialog()
                        ->assertSee('No Ship matched the given criteria.');
                });

            $browser->blank();

            // Validate file no longer exists.
            Storage::disk('public')->assertMissing($path);
        });
    }

    /**
     * @test
     */
    public function file_can_be_detached_from_edit_attached_screen()
    {
        $this->defineApplicationStates('searchable');

        $this->browse(function (Browser $browser) {
            $captain = CaptainFactory::new()->create();
            $ship = ShipFactory::new()->create();

            $browser->loginAs(1)
                ->visit(Attach::belongsToMany('captains', $captain->id, 'ships'))
                ->searchFirstRelation('ships', $ship->id)
                ->attach('@contract', __DIR__.'/Fixtures/Document.pdf')
                ->create()
                ->waitForText('The resource was attached!');

            // Verify the photo in the information in the database...
            $captain = Captain::orderBy('id', 'desc')->first();
            $ship = $captain->ships()->get()->first();
            $this->assertNotNull($path = $ship->pivot->contract);
            Storage::disk('public')->assertExists($path);

            // Delete the file...
            $browser->visit(UpdateAttached::belongsToMany('captains', $captain->id, 'ships', $ship->id))
                ->whenAvailable('button[dusk="contract-delete-link"]', function ($browser) {
                    $browser->click('');
                })
                ->whenAvailable(new ConfirmUploadRemovalModalComponent(), function ($browser) {
                    $browser->confirm();
                })
                ->waitForText('The file was deleted!');

            $browser->blank();

            // Validate file no longer exists.
            Storage::disk('public')->assertMissing($path);
        });
    }
}
