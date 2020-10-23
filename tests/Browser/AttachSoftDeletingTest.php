<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\CaptainFactory;
use Database\Factories\ShipFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Attach;
use Laravel\Nova\Tests\DuskTestCase;

class AttachSoftDeletingTest extends DuskTestCase
{
    /**
     * @test
     */
    public function non_searchable_resource_can_be_attached()
    {
        $this->setupLaravel();

        $captain = CaptainFactory::new()->create();
        $ship = ShipFactory::new()->create();

        $this->browse(function (Browser $browser) use ($captain, $ship) {
            $browser->loginAs(User::find(1))
                    ->visit(new Attach('captains', $captain->id, 'ships'))
                    ->searchAndSelectFirstRelation('ships', $ship->id)
                    ->clickAttach();

            $this->assertCount(1, $captain->fresh()->ships);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function with_trashed_checkbox_is_respected_and_non_searchable_soft_deleted_resource_can_be_attached()
    {
        $this->setupLaravel();

        $captain = CaptainFactory::new()->create();
        $ship = ShipFactory::new()->create(['deleted_at' => now()]);

        $this->browse(function (Browser $browser) use ($captain, $ship) {
            $browser->loginAs(User::find(1))
                    ->visit(new Attach('captains', $captain->id, 'ships'))
                    ->withTrashedRelation('ships')
                    ->searchAndSelectFirstRelation('ships', $ship->id)
                    ->clickAttach();

            $this->assertCount(0, $captain->fresh()->ships);
            $this->assertCount(1, $captain->fresh()->ships()->withTrashed()->get());

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function searchable_resource_can_be_attached()
    {
        $this->setupLaravel();

        $captain = CaptainFactory::new()->create();
        $ship = ShipFactory::new()->create();

        $this->whileSearchable(function () use ($captain, $ship) {
            $this->browse(function (Browser $browser) use ($captain, $ship) {
                $browser->loginAs(User::find(1))
                        ->visit(new Attach('captains', $captain->id, 'ships'))
                        ->searchAndSelectFirstRelation('ships', $ship->id)
                        ->clickAttach();

                $this->assertCount(1, $captain->fresh()->ships);

                $browser->blank();
            });
        });
    }

    /**
     * @test
     */
    public function with_trashed_checkbox_is_respected_and_searchable_soft_deleted_resource_can_be_attached()
    {
        $this->whileSearchable(function () {
            $this->setupLaravel();

            $captain = CaptainFactory::new()->create();
            $ship = ShipFactory::new()->create(['deleted_at' => now()]);

            $this->browse(function (Browser $browser) use ($captain, $ship) {
                $browser->loginAs(User::find(1))
                        ->visit(new Attach('captains', $captain->id, 'ships'))
                        ->withTrashedRelation('ships')
                        ->searchAndSelectFirstRelation('ships', $ship->id)
                        ->clickAttach();

                $this->assertCount(0, $captain->fresh()->ships);
                $this->assertCount(1, $captain->fresh()->ships()->withTrashed()->get());

                $browser->blank();
            });
        });
    }
}
