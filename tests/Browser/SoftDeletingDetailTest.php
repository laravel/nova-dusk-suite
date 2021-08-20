<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Dock;
use App\Models\User;
use Database\Factories\DockFactory;
use Database\Factories\ShipFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\Index;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Tests\DuskTestCase;

class SoftDeletingDetailTest extends DuskTestCase
{
    /**
     * @test
     */
    public function can_view_resource_attributes()
    {
        DockFactory::new()->create(['name' => 'Test Dock']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', 1))
                    ->assertSee('Test Dock');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_run_actions_on_resource()
    {
        DockFactory::new()->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', 1))
                    ->runAction('mark-as-active')
                    ->waitForText('The action ran successfully!');

            $this->assertEquals(1, Dock::find(1)->active);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_navigate_to_edit_page()
    {
        DockFactory::new()->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', 1))
                    ->edit()
                    ->on(new Update('docks', 1))
                    ->assertSeeIn('h1', 'Update Dock');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function resource_can_be_deleted()
    {
        DockFactory::new()->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', 1))
                    ->delete()
                    ->on(new Detail('docks', 1));

            $this->assertEquals(1, Dock::onlyTrashed()->count());

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function resource_can_be_restored()
    {
        DockFactory::new()->create(['deleted_at' => now()]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', 1))
                    ->restore()
                    ->waitForText('The dock was restored!')
                    ->on(new Detail('docks', 1));

            $this->assertEquals(1, Dock::count());

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function resource_can_be_edited_on_soft_deleted()
    {
        DockFactory::new()->create([
            'name' => 'hello',
            'deleted_at' => now(),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
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

    /**
     * @test
     */
    public function resource_can_run_action_on_soft_deleted()
    {
        DockFactory::new()->create([
            'name' => 'hello',
            'deleted_at' => now(),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', 1))
                    ->runAction('mark-as-active')
                    ->waitForText('The action ran successfully!', 25);

            $browser->blank();

            $dock = Dock::onlyTrashed()->find(1);
            $this->assertEquals(true, $dock->active);
        });
    }

    /**
     * @test
     */
    public function resource_can_be_force_deleted()
    {
        DockFactory::new()->create(['deleted_at' => now()]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', 1))
                    ->forceDelete()
                    ->on(new Index('docks'));

            $this->assertEquals(0, Dock::count());

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function relationships_can_be_searched()
    {
        $dock = DockFactory::new()->create();
        $dock->ships()->save($ship = ShipFactory::new()->create());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->waitForTable(25)
                                ->assertSeeResource(1)
                                ->searchFor('No Matching Ships')
                                ->assertDontSeeResource(1);
                    });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function soft_deleting_resources_can_be_manipulated_from_their_child_index()
    {
        $dock = DockFactory::new()->create();
        $dock->ships()->save($ship = ShipFactory::new()->create());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->withTrashed();

                        $browser->waitForTable()
                                ->assertSeeResource(1)
                                ->deleteResourceById(1)
                                ->waitForTable()
                                ->restoreResourceById(1)
                                ->waitForTable()
                                ->assertSeeResource(1);
                    });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_navigate_to_create_relationship_screen()
    {
        $dock = DockFactory::new()->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', 1))
                    ->runCreateRelation('ships')
                    ->assertQueryStringHas('viaResource', 'docks')
                    ->assertQueryStringHas('viaResourceId', '1')
                    ->assertQueryStringHas('viaRelationship', 'ships');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function relations_can_be_paginated()
    {
        $dock = DockFactory::new()->create();
        $dock->ships()->saveMany(ShipFactory::new()->times(10)->create());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->waitForTable(25)
                                ->assertSeeResource(10)
                                ->assertDontSeeResource(1)
                                ->nextPage()
                                ->assertDontSeeResource(10)
                                ->assertSeeResource(1)
                                ->previousPage()
                                ->assertSeeResource(10)
                                ->assertDontSeeResource(1);
                    });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function relations_can_be_sorted()
    {
        $dock = DockFactory::new()->create();
        $dock->ships()->saveMany(ShipFactory::new()->times(10)->create());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->waitForTable(25)
                                ->assertSeeResource(10)
                                ->assertSeeResource(6)
                                ->assertDontSeeResource(1)
                                ->sortBy('id')
                                ->assertDontSeeResource(10)
                                ->assertDontSeeResource(6)
                                ->assertSeeResource(5)
                                ->assertSeeResource(1);
                    });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function actions_on_all_matching_relations_should_be_scoped_to_the_relation()
    {
        $dock = DockFactory::new()->create();
        $dock->ships()->save($ship = ShipFactory::new()->create());

        $dock2 = DockFactory::new()->create();
        $dock2->ships()->save($ship2 = ShipFactory::new()->create());

        $this->browse(function (Browser $browser) use ($ship, $ship2) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->waitForTable(25)
                                ->selectAllMatching()
                                ->runAction('mark-as-active');
                    });

            $this->assertEquals(1, $ship->fresh()->active);
            $this->assertEquals(0, $ship2->fresh()->active);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function deleting_all_matching_relations_is_scoped_to_the_relationships()
    {
        $dock = DockFactory::new()->create();
        $dock->ships()->save($ship = ShipFactory::new()->create());

        $dock2 = DockFactory::new()->create();
        $dock2->ships()->save($ship2 = ShipFactory::new()->create());

        $this->browse(function (Browser $browser) use ($ship, $ship2) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->waitForTable(25)
                                ->selectAllMatching()
                                ->deleteSelected();
                    });

            $this->assertNotNull($ship->fresh()->deleted_at);
            $this->assertNull($ship2->fresh()->deleted_at);

            $browser->blank();
        });
    }
}
