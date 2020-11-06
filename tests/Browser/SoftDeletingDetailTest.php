<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\Dock;
use App\Models\User;
use Database\Factories\DockFactory;
use Database\Factories\ShipFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class SoftDeletingDetailTest extends DuskTestCase
{
    /**
     * @test
     */
    public function can_view_resource_attributes()
    {
        $this->setupLaravel();

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
        $this->setupLaravel();

        DockFactory::new()->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', 1))
                    ->runAction('mark-as-active');

            $this->assertEquals(1, Dock::find(1)->active);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_navigate_to_edit_page()
    {
        $this->setupLaravel();

        DockFactory::new()->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', 1))
                    ->click('@edit-resource-button')
                    ->pause(250)
                    ->assertPathIs('/nova/resources/docks/1/edit');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function resource_can_be_deleted()
    {
        $this->setupLaravel();

        DockFactory::new()->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', 1))
                    ->delete();

            $browser->assertPathIs('/nova/resources/docks/1');

            $this->assertEquals(1, Dock::onlyTrashed()->count());

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function resource_can_be_restored()
    {
        $this->setupLaravel();

        DockFactory::new()->create(['deleted_at' => now()]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', 1))
                    ->restore()
                    ->waitForText('The dock was restored!', 10)
                    ->assertPathIs('/nova/resources/docks/1');

            $this->assertEquals(1, Dock::count());

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function resource_can_be_force_deleted()
    {
        $this->setupLaravel();

        DockFactory::new()->create(['deleted_at' => now()]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', 1))
                    ->forceDelete();

            $browser->assertPathIs('/nova/resources/docks');

            $this->assertEquals(0, Dock::count());

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function relationships_can_be_searched()
    {
        $this->setupLaravel();

        $dock = DockFactory::new()->create();
        $dock->ships()->save($ship = ShipFactory::new()->create());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->assertSeeResource(1)
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
        $this->setupLaravel();

        $dock = DockFactory::new()->create();
        $dock->ships()->save($ship = ShipFactory::new()->create());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->withTrashed();

                        $browser->assertSeeResource(1)
                                ->deleteResourceById(1)
                                ->restoreResourceById(1)
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
        $this->setupLaravel();

        $dock = DockFactory::new()->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->click('@create-button')
                                ->assertPathIs('/nova/resources/ships/new')
                                ->assertQueryStringHas('viaResource', 'docks')
                                ->assertQueryStringHas('viaResourceId', '1')
                                ->assertQueryStringHas('viaRelationship', 'ships');
                    });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function relations_can_be_paginated()
    {
        $this->setupLaravel();

        $dock = DockFactory::new()->create();
        $dock->ships()->saveMany(ShipFactory::new()->times(10)->create());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->assertSeeResource(10)
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
        $this->setupLaravel();

        $dock = DockFactory::new()->create();
        $dock->ships()->saveMany(ShipFactory::new()->times(10)->create());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->assertSeeResource(10)
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
        $this->setupLaravel();

        $dock = DockFactory::new()->create();
        $dock->ships()->save($ship = ShipFactory::new()->create());

        $dock2 = DockFactory::new()->create();
        $dock2->ships()->save($ship2 = ShipFactory::new()->create());

        $this->browse(function (Browser $browser) use ($ship, $ship2) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->selectAllMatching()
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
        $this->setupLaravel();

        $dock = DockFactory::new()->create();
        $dock->ships()->save($ship = ShipFactory::new()->create());

        $dock2 = DockFactory::new()->create();
        $dock2->ships()->save($ship2 = ShipFactory::new()->create());

        $this->browse(function (Browser $browser) use ($ship, $ship2) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->selectAllMatching()
                                ->deleteSelected();
                    });

            $this->assertNotNull($ship->fresh()->deleted_at);
            $this->assertNull($ship2->fresh()->deleted_at);

            $browser->blank();
        });
    }
}
