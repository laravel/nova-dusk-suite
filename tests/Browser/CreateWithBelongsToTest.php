<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\DockFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Testing\Browser\Components\IndexComponent;
use Laravel\Nova\Testing\Browser\Pages\Create;
use Laravel\Nova\Testing\Browser\Pages\Detail;
use Laravel\Nova\Tests\DuskTestCase;

class CreateWithBelongsToTest extends DuskTestCase
{
    /**
     * @test
     */
    public function resource_can_be_created()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Create('posts'))
                    ->waitFor('.content form')
                    ->select('@user', 1)
                    ->type('@title', 'Test Post')
                    ->type('@body', 'Test Post Body')
                    ->create();

            $user = User::find(1);
            $post = $user->posts->first();
            $this->assertEquals('Test Post', $post->title);
            $this->assertEquals('Test Post Body', $post->body);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function parent_resource_should_be_locked_when_creating_via_parents_detail_page()
    {
        $user = User::find(1);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('users', 1))
                    ->within(new IndexComponent('posts'), function ($browser) {
                        $browser->waitFor('@create-button')->click('@create-button');
                    })
                    ->on(new Create('posts'))
                    ->waitFor('.content form')
                    ->assertDisabled('@user')
                    ->type('@title', 'Test Post')
                    ->type('@body', 'Test Post Body')
                    ->create();

            $user = User::find(1);
            $post = $user->posts->first();
            $this->assertEquals('Test Post', $post->title);
            $this->assertEquals('Test Post Body', $post->body);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function searchable_resource_can_be_created()
    {
        $dock = DockFactory::new()->create();

        $this->browse(function (Browser $browser) use ($dock) {
            $browser->loginAs(User::find(1))
                    ->visit(new Create('ships'))
                    ->searchAndSelectFirstRelation('docks', '1')
                    ->type('@name', 'Test Ship')
                    ->create();

            $this->assertCount(1, $dock->fresh()->ships);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function searchable_parent_resource_should_be_locked_when_creating_via_parents_detail_page()
    {
        $dock = DockFactory::new()->create();

        $this->browse(function (Browser $browser) use ($dock) {
            $browser->loginAs(User::find(1))
                    ->visit(new Detail('docks', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->waitFor('@create-button')->click('@create-button');
                    })
                    ->on(new Create('ships'))
                    ->waitFor('.content form')
                    ->assertDisabled('@dock')
                    ->type('@name', 'Test Ship')
                    ->create();

            $this->assertCount(1, $dock->fresh()->ships);

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function belongs_to_field_should_honor_custom_labels_on_create()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Create('invoice-items'))
                    ->waitFor('.content form')
                    ->waitFor('.content form')
                    ->assertSeeIn('.content', 'Create Invoice Item');

            $browser->blank();
        });
    }

    /**
     * @test
     */
    public function belongs_to_field_should_honor_query_parameters_on_create()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Create('posts', [
                        'viaResource' => 'users',
                        'viaResourceId' => 1,
                        'viaRelationship' => 'posts',
                    ]))
                    ->waitFor('.content form')
                    ->assertValue('@user', 1);

            $browser->blank();
        });
    }
}
