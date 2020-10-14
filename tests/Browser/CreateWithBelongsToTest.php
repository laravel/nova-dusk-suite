<?php

namespace Laravel\Nova\Tests\Browser;

use App\Models\User;
use Database\Factories\DockFactory;
use Laravel\Dusk\Browser;
use Laravel\Nova\Tests\Browser\Components\IndexComponent;
use Laravel\Nova\Tests\DuskTestCase;

class CreateWithBelongsToTest extends DuskTestCase
{
    /**
     * @test
     */
    public function resource_can_be_created()
    {
        $this->setupLaravel();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Create('posts'))
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
        $this->setupLaravel();

        $user = User::find(1);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->within(new IndexComponent('posts'), function ($browser) {
                        $browser->click('@create-button');
                    })
                    ->on(new Pages\Create('posts'))
                    ->pause(175)
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
        $this->setupLaravel();

        $dock = DockFactory::new()->create();

        $this->browse(function (Browser $browser) use ($dock) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Create('ships'))
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
        $this->setupLaravel();

        $dock = DockFactory::new()->create();

        $this->browse(function (Browser $browser) use ($dock) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('docks', 1))
                    ->waitFor('@ships-index-component', 5)
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->click('@create-button');
                    })
                    ->on(new Pages\Create('ships'))
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
        $this->setupLaravel();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Create('invoice-items'))
                    ->assertSee('Client Invoice');

            $browser->blank();
        });
    }
}
