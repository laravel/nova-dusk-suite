<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Dock;
use Database\Factories\DockFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\Index;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Tests\DuskTestCase;

class SoftDeletingDetailTest extends DuskTestCase
{
    public function test_resource_can_interacts_with()
    {
        DockFactory::new()->create(['name' => 'Test Dock']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('docks', 1))
                ->assertSee('Test Dock');

            $browser->visit(new Detail('docks', 1))
                ->runAction('mark-as-active')
                ->waitForText('The action was executed successfully.');

            $browser->visit(new Detail('docks', 1))
                ->edit()
                ->on(new Update('docks', 1))
                ->assertSeeIn('h1', 'Update Dock');

            $browser->visit(new Detail('docks', 1))
                ->delete()
                ->waitForText('The dock was deleted!')
                ->on(new Detail('docks', 1));

            $this->assertEquals(1, Dock::onlyTrashed()->count());

            $browser->visit(new Detail('docks', 1))
                ->restore()
                ->waitForText('The dock was restored!')
                ->on(new Detail('docks', 1));

            $this->assertEquals(1, Dock::count());

            $browser->blank();
        });
    }

    public function test_resource_can_be_edited_on_soft_deleted()
    {
        DockFactory::new()->create([
            'name' => 'hello',
            'deleted_at' => now(),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Update('docks', 1))
                ->type('@name', 'world')
                ->update()
                ->waitForText('The dock was updated!')
                ->on(new Detail('docks', 1));

            $browser->blank();

            $dock = Dock::onlyTrashed()->find(1);
            $this->assertEquals('world', $dock->name);
        });
    }

    public function test_resource_can_run_action_on_soft_deleted()
    {
        DockFactory::new()->create([
            'name' => 'hello',
            'deleted_at' => now(),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('docks', 1))
                ->runAction('mark-as-active')
                ->waitForText('The action was executed successfully.');

            $browser->blank();

            $dock = Dock::onlyTrashed()->find(1);
            $this->assertEquals(true, $dock->active);
        });
    }

    public function test_resource_can_be_force_deleted()
    {
        DockFactory::new()->create(['deleted_at' => now()]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('docks', 1))
                ->forceDelete()
                ->on(new Index('docks'));

            $this->assertEquals(0, Dock::count());

            $browser->blank();
        });
    }
}
