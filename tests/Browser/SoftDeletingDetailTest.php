<?php

namespace Tests\Browser;

use App\Dock;
use App\Post;
use App\Ship;
use App\User;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\IndexComponent;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class SoftDeletingDetailTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function can_view_resource_attributes()
    {
        $this->seed();

        factory(Dock::class)->create(['name' => 'Test Dock']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('docks', 1))
                    ->assertSee('Test Dock');
        });
    }

    /**
     * @test
     */
    public function can_run_actions_on_resource()
    {
        $this->seed();

        factory(Dock::class)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('docks', 1))
                    ->runAction('mark-as-active');

            $this->assertEquals(1, Dock::find(1)->active);
        });
    }

    /**
     * @test
     */
    public function can_navigate_to_edit_page()
    {
        $this->seed();

        factory(Dock::class)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('docks', 1))
                    ->click('@edit-resource-button')
                    ->pause(250)
                    ->assertPathIs('/nova/resources/docks/1/edit');
        });
    }

    /**
     * @test
     */
    public function resource_can_be_deleted()
    {
        $this->seed();

        factory(Dock::class)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('docks', 1))
                    ->delete();

            $browser->assertPathIs('/nova/resources/docks/1');

            $this->assertEquals(1, Dock::onlyTrashed()->count());
        });
    }

    /**
     * @test
     */
    public function resource_can_be_restored()
    {
        $this->seed();

        factory(Dock::class)->create(['deleted_at' => now()]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('docks', 1))
                    ->restore();

            $browser->assertPathIs('/nova/resources/docks/1');

            $this->assertEquals(1, Dock::count());
        });
    }

    /**
     * @test
     */
    public function resource_can_be_force_deleted()
    {
        $this->seed();

        factory(Dock::class)->create(['deleted_at' => now()]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('docks', 1))
                    ->forceDelete();

            $browser->assertPathIs('/nova/resources/docks');

            $this->assertEquals(0, Dock::count());
        });
    }

    /**
     * @test
     */
    public function relationships_can_be_searched()
    {
        $this->seed();

        $dock = factory(Dock::class)->create();
        $dock->ships()->save($ship = factory(Ship::class)->create());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('docks', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->assertSeeResource(1)
                                ->searchFor('No Matching Ships')
                                ->assertDontSeeResource(1);
                    });
        });
    }

    /**
     * @test
     */
    public function can_navigate_to_create_relationship_screen()
    {
        $this->seed();

        $dock = factory(Dock::class)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('docks', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->click('@create-button')
                                ->assertPathIs('/nova/resources/ships/new')
                                ->assertQueryStringHas('viaResource', 'docks')
                                ->assertQueryStringHas('viaResourceId', '1')
                                ->assertQueryStringHas('viaRelationship', 'ships');
                    });
        });
    }

    /**
     * @test
     */
    public function relations_can_be_paginated()
    {
        $this->seed();

        $dock = factory(Dock::class)->create();
        $dock->ships()->saveMany(factory(Ship::class, 10)->create());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('docks', 1))
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
        });
    }

    /**
     * @test
     */
    public function relations_can_be_sorted()
    {
        $this->seed();

        $dock = factory(Dock::class)->create();
        $dock->ships()->saveMany(factory(Ship::class, 10)->create());

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('docks', 1))
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
        });
    }

    /**
     * @test
     */
    public function actions_on_all_matching_relations_should_be_scoped_to_the_relation()
    {
        $this->seed();

        $dock = factory(Dock::class)->create();
        $dock->ships()->save($ship = factory(Ship::class)->create());

        $dock2 = factory(Dock::class)->create();
        $dock2->ships()->save($ship2 = factory(Ship::class)->create());

        $this->browse(function (Browser $browser) use ($ship, $ship2) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('docks', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->selectAllMatching()
                                ->runAction('mark-as-active');
                    });

            $this->assertEquals(1, $ship->fresh()->active);
            $this->assertEquals(0, $ship2->fresh()->active);
        });
    }

    /**
     * @test
     */
    public function deleting_all_matching_relations_is_scoped_to_the_relationships()
    {
        $this->seed();

        $dock = factory(Dock::class)->create();
        $dock->ships()->save($ship = factory(Ship::class)->create());

        $dock2 = factory(Dock::class)->create();
        $dock2->ships()->save($ship2 = factory(Ship::class)->create());

        $this->browse(function (Browser $browser) use ($ship, $ship2) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('docks', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->selectAllMatching()
                                ->deleteSelected();
                    });

            $this->assertNull($ship->fresh());
            $this->assertNotNull($ship2->fresh());
        });
    }
}
