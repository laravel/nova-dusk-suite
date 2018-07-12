<?php

namespace Tests\Browser;

use App\Dock;
use App\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\IndexComponent;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class CreateWithSearchableSoftDeletingBelongsToTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function searchable_belongs_to_respects_with_trashed_checkbox_state()
    {
        $this->seed();

        $dock = factory(Dock::class)->create(['deleted_at' => now()]);

        $this->browse(function (Browser $browser) use ($dock) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Create('ships'))
                    ->searchRelation('docks', '1')
                    ->assertNoRelationSearchResults('docks')
                    ->withTrashedRelation('docks')
                    ->searchRelation('docks', '1')
                    ->selectCurrentRelation('docks')
                    ->type('@name', 'Test Ship')
                    ->create();

            $this->assertCount(1, $dock->fresh()->ships);
        });
    }
}
