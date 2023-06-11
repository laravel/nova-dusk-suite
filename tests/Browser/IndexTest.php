<?php

namespace Laravel\Nova\Tests\Browser;

use Database\Factories\PostFactory;
use Database\Factories\RoleFactory;
use Database\Factories\UserFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Contracts\QueryBuilder;
use Laravel\Nova\Testing\Browser\Components\BreadcrumbComponent;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Testing\Browser\Pages\Dashboard;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\Index;
use Laravel\Nova\Testing\Browser\Pages\Page;
use Laravel\Nova\Testing\Browser\Pages\Update;
use Laravel\Nova\Testing\Browser\Pages\UserIndex;
use Laravel\Nova\Tests\DuskTestCase;

class IndexTest extends DuskTestCase
{
    public function test_resource_index_can_be_viewed()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex)
                ->within(new BreadcrumbComponent(), function ($browser) {
                    $browser->assertSee('Resources')
                        ->assertCurrentPageTitle('Users');
                })
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->assertSeeResource(1)
                        ->assertSeeResource(2)
                        ->assertSeeResource(3)
                        ->assertSee('1-4 of 4');
                })
                ->assertTitle('Nova Site - Users');

            $browser->blank();
        });
    }

    public function test_resource_index_cant_be_viewed_on_invalid_resource()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Page('/resources/foobar'))
                ->assertNotFound();

            $browser->blank();
        });
    }

    public function test_resource_index_can_show_reload_button_when_received_errors()
    {
        $this->beforeServingApplication(function ($app) {
            $app->bind(QueryBuilder::class, function () {
                throw new \Exception('502');
            });
        });

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForText('Failed to load Users!')
                        ->assertSee('Reload');
                })
                ->assertTitle('Nova Site - Users');

            $browser->blank();
        });

        $this->removeApplicationTweaks();
    }

    public function test_can_navigate_to_different_screens()
    {
        $this->browse(function (Browser $browser) {
            $post = PostFactory::new()->create();

            $browser->loginAs(1);

            // to Create Resource screen
            $browser->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitFor('@create-button')->click('@create-button');
                })
                ->on(new Create('users'))
                ->assertSeeIn('h1', 'Create User')
                ->assertSee('Create & Add Another')
                ->assertSee('Create User');

            // To Create Resource screen using shortcut
            $browser->visit(new UserIndex)
                ->waitFor('@users-index-component')
                ->keys('', ['c'])
                ->on(new Create('users'))
                ->assertSeeIn('h1', 'Create User')
                ->assertSee('Create & Add Another')
                ->assertSee('Create User');

            // to different Resource Index screen
            $browser->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTextIn('h1', 'Users')
                        ->assertSee('James Brooks')
                        ->assertSee('David Hemphill');
                });

            $browser->script([
                'Nova.visit("/resources/posts");',
            ]);

            $browser->on(new Index('posts'))
                ->within(new IndexComponent('posts'), function ($browser) use ($post) {
                    $browser->assertSeeIn('h1', 'User Post')
                        ->assertSee($post->title)
                        ->assertDontSee('James Brooks')
                        ->assertDontSee('David Hemphill');
                });

            // to Resource Detail screen
            $browser->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->viewResourceById(1);
                })
                ->on(new Detail('users', 1))
                ->assertSeeIn('h1', 'User Details');

            // to Resource Edit screen
            $browser->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->editResourceById(1);
                })
                ->on(new Update('users', 1))
                ->assertSeeIn('h1', 'Update User');

            $browser->blank();
        });
    }

    public function test_correct_select_all_matching_count_is_displayed()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->assertSee('1-4 of 4')
                        ->assertSelectAllMatchingCount(4)
                        ->closeCurrentDropdown()
                        ->searchFor('Taylor')
                        ->waitForTable()
                        ->assertSelectAllMatchingCount(1)
                        ->assertSee('1-1 of 1');
                });

            $browser->blank();
        });
    }

    public function test_resources_can_be_sorted_by_id()
    {
        UserFactory::new()->times(50)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->assertSeeResource(50)
                        ->assertSeeResource(36)
                        ->assertDontSeeResource(25)
                        ->assertSee('1-25 of 54');

                    $browser->sortBy('id')
                        ->assertDontSeeResource(50)
                        ->assertDontSeeResource(26)
                        ->assertSeeResource(25)
                        ->assertSeeResource(1)
                        ->assertSee('1-25 of 54');
                });

            $browser->blank();
        });
    }

    public function test_resources_can_be_resorted_by_different_field_default_to_ascending_first()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->assertSee('1-4 of 4')
                        ->assertSeeIn('table > tbody > tr:first-child', 'Laravel Nova');

                    $browser->sortBy('name')
                        ->assertSeeIn('table > tbody > tr:first-child', 'David Hemphill')
                        ->sortBy('name')
                        ->assertSeeIn('table > tbody > tr:first-child', 'Taylor Otwell')
                        ->sortBy('email')
                        ->assertSeeIn('table > tbody > tr:first-child', 'David Hemphill');
                });

            $browser->blank();
        });
    }

    public function test_resources_can_be_paginated()
    {
        UserFactory::new()->times(50)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new UserIndex)
                ->within(new IndexComponent('users'), function ($browser) {
                    $browser->waitForTable()
                        ->assertSeeResource(50)
                        ->assertSeeResource(30)
                        ->assertDontSeeResource(25)
                        ->assertSee('1-25 of 54');

                    $browser->nextPage()
                        ->assertDontSeeResource(50)
                        ->assertDontSeeResource(30)
                        ->assertSeeResource(25)
                        ->assertDontSeeResource(1)
                        ->assertSee('26-50 of 54');

                    $browser->previousPage()
                        ->assertSeeResource(50)
                        ->assertSeeResource(30)
                        ->assertDontSeeResource(25)
                        ->assertDontSeeResource(1)
                        ->assertSee('1-25 of 54');
                });

            $browser->blank();
        });
    }

    public function test_resource_index_can_show_contents_even_when_set_as_collapsed()
    {
        $role = RoleFactory::new()->create();

        $this->browse(function (Browser $browser) use ($role) {
            $browser->loginAs(1)
                ->visit(new Dashboard());

            $browser->script('localStorage.setItem("nova.resources.roles.collapsed", true)');

            $browser->visit(new Index('roles'))
                ->within(new IndexComponent('roles'), function ($browser) use ($role) {
                    $browser->assertSee($role->name);
                });

            $browser->blank();
        });
    }
}
