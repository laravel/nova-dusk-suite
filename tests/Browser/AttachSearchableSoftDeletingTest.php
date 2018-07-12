<?php

namespace Tests\Browser;

use App\Role;
use App\Ship;
use App\User;
use App\Captain;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\IndexComponent;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AttachSearchableSoftDeletingTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function resource_can_be_attached()
    {
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
    }

    /**
     * @test
     */
    public function soft_deleted_resource_can_be_attached()
    {
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
    }
}
