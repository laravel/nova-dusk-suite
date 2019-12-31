<?php

namespace Tests\Browser;

use App\Captain;
use App\Ship;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AttachSoftDeletingTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function non_searchable_resource_can_be_attached()
    {
        $this->seed();

        $captain = factory(Captain::class)->create();
        $ship = factory(Ship::class)->create();

        $this->browse(function (Browser $browser) use ($captain, $ship) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Attach('captains', $captain->id, 'ships'))
                    ->selectAttachable(1)
                    ->clickAttach();

            $this->assertCount(1, $captain->fresh()->ships);
        });
    }

    /**
     * @test
     */
    public function with_trashed_checkbox_is_respected_and_non_searchable_soft_deleted_resource_can_be_attached()
    {
        $this->seed();

        $captain = factory(Captain::class)->create();
        $ship = factory(Ship::class)->create(['deleted_at' => now()]);

        $this->browse(function (Browser $browser) use ($captain, $ship) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Attach('captains', $captain->id, 'ships'))
                    ->assertSelectMissingOption('@attachable-select', $ship->id)
                    ->withTrashedRelation('ships')
                    ->assertSelectHasOption('@attachable-select', $ship->id)
                    ->selectAttachable($ship->id)
                    ->clickAttach();

            $this->assertCount(0, $captain->fresh()->ships);
            $this->assertCount(1, $captain->fresh()->ships()->withTrashed()->get());
        });
    }

    /**
     * @test
     */
    public function searchable_resource_can_be_attached()
    {
        $this->whileSearchable(function () {
            $this->seed();

            $captain = factory(Captain::class)->create();
            $ship = factory(Ship::class)->create();

            $this->browse(function (Browser $browser) use ($captain, $ship) {
                $browser->loginAs(User::find(1))
                        ->visit(new Pages\Attach('captains', $captain->id, 'ships'))
                        ->searchRelation('ships', 1)
                        ->selectCurrentRelation('ships')
                        ->clickAttach();

                $this->assertCount(1, $captain->fresh()->ships);
            });
        });
    }

    /**
     * @test
     */
    public function with_trashed_checkbox_is_respected_and_searchable_soft_deleted_resource_can_be_attached()
    {
        $this->whileSearchable(function () {
            $this->seed();

            $captain = factory(Captain::class)->create();
            $ship = factory(Ship::class)->create(['deleted_at' => now()]);

            $this->browse(function (Browser $browser) use ($captain, $ship) {
                $browser->loginAs(User::find(1))
                        ->visit(new Pages\Attach('captains', $captain->id, 'ships'))
                        ->withTrashedRelation('ships')
                        ->searchRelation('ships', 1)
                        ->selectCurrentRelation('ships')
                        ->clickAttach();

                $this->assertCount(0, $captain->fresh()->ships);
                $this->assertCount(1, $captain->fresh()->ships()->withTrashed()->get());
            });
        });
    }
}
