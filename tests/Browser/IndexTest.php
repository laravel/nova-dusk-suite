<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\PostFactory;
use Database\Factories\UserFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Contracts\QueryBuilder;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Index;
use Laravel\Nova\Testing\Browser\Pages\UserIndex;
use Laravel\Nova\Tests\DuskTestCase;

class IndexTest extends DuskTestCase
{
    protected function tearDown(): void
    {
        $this->removeApplicationTweaks();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function resource_index_can_be_viewed()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                                ->assertSeeResource(1)
                                ->assertSeeResource(2)
                                ->assertSeeResource(3)
                                ->assertSee('1-4 of 4');
                    })
                    ->assertTitle('Users | Nova Site');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function resource_index_cant_be_viewed_on_invalid_resource()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new Index('foobar'))
                    ->waitForText('404', 15)
                    ->assertPathIs('/nova/404');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function resource_index_can_show_reload_button_when_received_errors()
    {
        $this->tweakApplication(function ($app) {
            $app->bind(QueryBuilder::class, function () {
                abort(502);
            });
        });

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForText('Failed to load Users!')
                            ->assertSee('Reload');
                    })
                    ->assertTitle('Users | Nova Site');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function can_navigate_to_different_screens()
    {
        $this->browse(function (Browser $browser) {
            $post = PostFactory::new()->create();

            $browser->loginAs(1);

            // to Create Resource screen
            $browser->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitFor('@create-button')->click('@create-button');
                    })
                    ->waitForTextIn('h1', 'Create User')
                    ->assertPathIs('/nova/resources/users/new')
                    ->assertSee('Create & Add Another')
                    ->assertSee('Create User');

            // To Create Resource screen using shortcut
            $browser->visit(new UserIndex)
                    ->waitFor('@users-index-component')
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitFor('@create-button');
                    })
                    ->keys('', ['c'])
                    ->waitForTextIn('h1', 'Create User')
                    ->assertPathIs('/nova/resources/users/new')
                    ->assertSee('Create & Add Another')
                    ->assertSee('Create User');

            // to different Resource Index screen
            $browser->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTextIn('h1', 'Users', 25)
                            ->assertSee('Mohamed Said')
                            ->assertSee('David Hemphill');
                    });

            $browser->script([
                'Nova.app.$router.push({ name: "index", params: { resourceName: "posts" }});',
            ]);

            $browser->waitForTextIn('h1', 'User Post', 25)
                    ->within(new IndexComponent('posts'), function ($browser) use ($post) {
                        $browser->assertSee($post->title)
                            ->assertDontSee('Mohamed Said')
                            ->assertDontSee('David Hemphill');
                    })->assertPathIs('/nova/resources/posts');

            // to Resource Detail screen
            $browser->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                                ->click('@1-view-button');
                    })
                    ->waitForText('User Details', 25)
                    ->assertSee('User Details')
                    ->assertPathIs('/nova/resources/users/1');

            // to Resource Edit screen
            $browser->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                                ->click('@1-edit-button');
                    })
                    ->waitForText('Update User', 25)
                    ->assertSee('Update User')
                    ->assertPathIs('/nova/resources/users/1/edit');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function resources_can_be_searched()
    {
        $this->browse(function (Browser $browser) {
            // Search For Single User By ID...
            $browser->loginAs(1)
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                                ->searchFor('3')
                                ->assertDontSeeResource(1)
                                ->assertDontSeeResource(2)
                                ->assertSeeResource(3)
                                ->assertSee('1-1 of 1');
                    });

            // Search For Single User By Name...
            $browser->loginAs(1)
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                                ->searchFor('Taylor')
                                ->assertSeeResource(1)
                                ->assertDontSeeResource(2)
                                ->assertDontSeeResource(3);
                    });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function resources_search_query_will_reset_on_revisit()
    {
        $this->browse(function (Browser $browser) {
            // Search For Single User By ID...
            $browser->loginAs(1)
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                                ->searchFor('3')
                                ->assertDontSeeResource(1)
                                ->assertDontSeeResource(2)
                                ->assertSeeResource(3)
                                ->assertDontSeeResource(4)
                                ->assertValue('@search', '3');
                    })
                    ->click('@users-resource-link')
                    ->waitForTextIn('h1', 'Users', 25)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                                ->assertValue('@search', '')
                                ->assertSeeResource(1)
                                ->assertSeeResource(2)
                                ->assertSeeResource(3)
                                ->assertSeeResource(4);
                    });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function test_correct_select_all_matching_count_is_displayed()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
                                ->assertSee('1-4 of 4')
                                ->assertSelectAllMatchingCount(4)
                                ->click('')
                                ->searchFor('Taylor')
                                ->assertSelectAllMatchingCount(1)
                                ->assertSee('1-1 of 1');
                    });

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function resources_can_be_sorted_by_id()
    {
        UserFactory::new()->times(50)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
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

    /**
     * @test
     */
    public function resources_can_be_resorted_by_different_field_default_to_ascending_first()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
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

    /**
     * @test
     */
    public function resources_can_be_paginated()
    {
        UserFactory::new()->times(50)->create();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                    ->visit(new UserIndex)
                    ->within(new IndexComponent('users'), function ($browser) {
                        $browser->waitForTable(25)
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
}
