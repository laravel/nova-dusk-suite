<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\DockFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\BreadcrumbComponent;
use Laravel\Nova\Testing\Browser\Components\FormComponent;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Testing\Browser\Pages\NotFound;
use Laravel\Nova\Tests\DuskTestCase;

class CreateWithBelongsToTest extends DuskTestCase
{
    public function test_resource_can_be_created()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Create('posts'))
                ->within(new FormComponent(), function ($browser) {
                    $browser->type('@title', 'Test Post')
                        ->type('@body', 'Test Post Body')
                        ->selectRelation('user', 1);
                })
                ->create()
                ->waitForText('The user post was created!');

            $user = User::with('posts')->find(1);
            $post = $user->posts->first();
            $this->assertEquals('Test Post', $post->title);
            $this->assertEquals('Test Post Body', $post->body);

            $browser->blank();
        });
    }

    public function test_parent_resource_should_be_locked_when_creating_via_parents_detail_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Detail('users', 1))
                ->runCreateRelation('posts')
                ->within(new FormComponent(), function ($browser) {
                    $browser->assertDisabled('select[dusk="user"]')
                        ->type('@title', 'Test Post')
                        ->type('@body', 'Test Post Body');
                })
                ->create()
                ->waitForText('The user post was created!');

            $user = User::with('posts')->find(1);
            $post = $user->posts->first();
            $this->assertEquals('Test Post', $post->title);
            $this->assertEquals('Test Post Body', $post->body);

            $browser->blank();
        });
    }

    public function test_searchable_resource_can_be_created()
    {
        $dock = DockFactory::new()->create();

        $this->browse(function (Browser $browser) use ($dock) {
            $browser->loginAs(1)
                ->visit(new Create('ships'))
                ->within(new FormComponent(), function ($browser) {
                    $browser->searchFirstRelation('docks', '1')
                        ->type('@name', 'Test Ship');
                })
                ->create()
                ->waitForText('The ship was created!');

            $this->assertCount(1, $dock->fresh()->ships);

            $browser->blank();
        });
    }

    public function test_searchable_parent_resource_should_be_locked_when_creating_via_parents_detail_page()
    {
        $dock = DockFactory::new()->create();

        $this->browse(function (Browser $browser) use ($dock) {
            $browser->loginAs(1)
                ->visit(new Detail('docks', 1))
                ->runCreateRelation('ships')
                ->within(new FormComponent(), function ($browser) {
                    $browser->whenAvailable('select[dusk="dock"]', function ($browser) {
                        $browser->assertDisabled('')
                                ->assertSelected('', 1);
                    })
                    ->type('@name', 'Test Ship');
                })
                ->create()
                ->waitForText('The ship was created!');

            $this->assertCount(1, $dock->fresh()->ships);

            $browser->blank();
        });
    }

    public function test_belongs_to_field_should_honor_custom_labels_on_create()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Create('invoice-items'))
                ->assertSeeIn('@nova-form', 'Create Invoice Item');

            $browser->blank();
        });
    }

    public function test_belongs_to_field_should_honor_query_parameters_on_create()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(1)
                ->visit(new Create('posts', [
                    'viaResource' => 'users',
                    'viaResourceId' => 1,
                    'viaRelationship' => 'posts',
                ]))
                ->waitForTextIn('@nova-form', 'Taylor Otwell')
                ->within(new BreadcrumbComponent(), function ($browser) {
                    $browser->assertSeeLink('Users')
                        ->assertSeeLink('User Details: 1')
                        ->assertCurrentPageTitle('Create User Post');
                })
                ->within(new FormComponent(), function ($browser) {
                    $browser->whenAvailable('select[dusk="user"]', function ($browser) {
                        $browser->assertDisabled('')
                            ->assertSelected('', 1);
                    });
                });

            $browser->blank();
        });
    }

    public function test_belongs_to_field_cannot_create_from_invalid_parents_detail_page()
    {
        $this->browse(function (Browser $browser) {
            $page = new Create('posts', [
                'viaResource' => 'users',
                'viaResourceId' => 99999,
                'viaRelationship' => 'posts',
            ]);

            $browser->loginAs(1)
                ->visit($page->url())
                ->on(new NotFound);

            $browser->blank();
        });
    }
}
