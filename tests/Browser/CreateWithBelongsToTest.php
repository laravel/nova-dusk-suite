<?php

namespace Tests\Browser;

use App\Dock;
use App\Post;
use App\User;
use App\InvoiceItem;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Tests\Browser\Components\IndexComponent;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class CreateWithBelongsToTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function resource_can_be_created()
    {
        $this->seed();

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
        });
    }

    /**
     * @test
     */
    public function parent_resource_should_be_locked_when_creating_via_parents_detail_page()
    {
        $this->seed();

        $user = User::find(1);

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('users', 1))
                    ->within(new IndexComponent('posts'), function ($browser) {
                        $browser->click('@create-button');
                    })
                    ->on(new Pages\Create('posts'))
                    ->assertDisabled('@user')
                    ->type('@title', 'Test Post')
                    ->type('@body', 'Test Post Body')
                    ->create();

            $user = User::find(1);
            $post = $user->posts->first();
            $this->assertEquals('Test Post', $post->title);
            $this->assertEquals('Test Post Body', $post->body);
        });
    }

    /**
     * @test
     */
    public function searchable_resource_can_be_created()
    {
        $this->seed();

        $dock = factory(Dock::class)->create();

        $this->browse(function (Browser $browser) use ($dock) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Create('ships'))
                    ->searchRelation('docks', '1')
                    ->selectCurrentRelation('docks')
                    ->type('@name', 'Test Ship')
                    ->create();

            $this->assertCount(1, $dock->fresh()->ships);
        });
    }

    /**
     * @test
     */
    public function searchable_parent_resource_should_be_locked_when_creating_via_parents_detail_page()
    {
        $this->seed();

        $dock = factory(Dock::class)->create();

        $this->browse(function (Browser $browser) use ($dock) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Detail('docks', 1))
                    ->within(new IndexComponent('ships'), function ($browser) {
                        $browser->click('@create-button');
                    })
                    ->on(new Pages\Create('ships'))
                    ->assertDisabled('@dock')
                    ->type('@name', 'Test Ship')
                    ->create();

            $this->assertCount(1, $dock->fresh()->ships);
        });
    }

    /**
     * @test
     */
    public function belongs_to_field_should_honor_custom_labels_on_create()
    {
        $this->seed();

        $this->browse(function (Browser $browser) {
            $browser->loginAs(User::find(1))
                    ->visit(new Pages\Create('invoice-items'))
                    ->assertSee('Client Invoice');
        });
    }
}
